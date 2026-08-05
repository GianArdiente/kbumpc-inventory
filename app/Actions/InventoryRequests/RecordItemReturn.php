<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\RequestItem;
use App\Models\ReturnRecord;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecordItemReturn
{
    public function execute(
        RequestItem $requestItem,
        User $recordedBy,
        int $goodQuantity,
        int $damagedQuantity,
        int $missingQuantity,
        ?string $remarks = null
    ): ReturnRecord {
        return DB::transaction(function () use (
            $requestItem,
            $recordedBy,
            $goodQuantity,
            $damagedQuantity,
            $missingQuantity,
            $remarks
        ) {
            $lockedRequestItem = RequestItem::query()
                ->with(['item', 'inventoryRequest'])
                ->lockForUpdate()
                ->findOrFail($requestItem->id);

            $item = $lockedRequestItem->item;

            $inventoryRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($lockedRequestItem->inventory_request_id);

            if ($recordedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot record returns.'
                );
            }

            if (! in_array($recordedBy->role, ['admin', 'staff'], true)) {
                throw new DomainException(
                    'Only Admin or Staff users may record returns.'
                );
            }

            if ($item->consumption_type !== 'non-consumable') {
                throw new DomainException(
                    'Returns may only be recorded for non-consumable items.'
                );
            }

            if (! in_array(
                $inventoryRequest->status,
                ['mobilized', 'in_use'],
                true
            )) {
                throw new DomainException(
                    'Returns may only be recorded for mobilized or in-use requests.'
                );
            }

            if (
                $goodQuantity < 0 ||
                $damagedQuantity < 0 ||
                $missingQuantity < 0
            ) {
                throw new DomainException(
                    'Return quantities cannot be negative.'
                );
            }

            $newAccountedQuantity =
                $goodQuantity +
                $damagedQuantity +
                $missingQuantity;

            if ($newAccountedQuantity < 1) {
                throw new DomainException(
                    'At least one returned, damaged, or missing item is required.'
                );
            }

            $previouslyAccounted = $lockedRequestItem
                ->returnRecords()
                ->lockForUpdate()
                ->get()
                ->sum(function (ReturnRecord $record): int {
                    return
                        $record->good_quantity +
                        $record->damaged_quantity +
                        $record->missing_quantity;
                });

            if (
                $previouslyAccounted + $newAccountedQuantity >
                $lockedRequestItem->released_quantity
            ) {
                throw new DomainException(
                    'Return quantities exceed the released quantity.'
                );
            }

            $returnRecord = ReturnRecord::create([
                'request_item_id' => $lockedRequestItem->id,
                'recorded_by' => $recordedBy->id,
                'good_quantity' => $goodQuantity,
                'damaged_quantity' => $damagedQuantity,
                'missing_quantity' => $missingQuantity,
                'returned_at' => now(),
                'remarks' => $remarks,
            ]);

            if ($goodQuantity > 0) {
                $item->increment(
                    'available_quantity',
                    $goodQuantity
                );

                $item->refresh();

                InventoryTransaction::create([
                    'item_id' => $item->id,
                    'user_id' => $recordedBy->id,
                    'source_type' => ReturnRecord::class,
                    'source_id' => $returnRecord->id,
                    'type' => 'good_return',
                    'overall_stock_change' => 0,
                    'available_quantity_change' => $goodQuantity,
                    'overall_stock_after' => $item->overall_stock,
                    'available_quantity_after' => $item->available_quantity,
                    'remarks' =>
                        "Good return for request {$inventoryRequest->request_number}.",
                ]);
            }

            ActivityLog::create([
                'user_id' => $recordedBy->id,
                'action' => 'item_return_recorded',
                'subject_type' => ReturnRecord::class,
                'subject_id' => $returnRecord->id,
                'description' => sprintf(
                    '%s recorded a return for %s.',
                    $recordedBy->name,
                    $item->name
                ),
                'properties' => [
                    'request_number' =>
                        $inventoryRequest->request_number,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'good_quantity' => $goodQuantity,
                    'damaged_quantity' => $damagedQuantity,
                    'missing_quantity' => $missingQuantity,
                    'previously_accounted' => $previouslyAccounted,
                    'total_accounted' =>
                        $previouslyAccounted + $newAccountedQuantity,
                    'released_quantity' =>
                        $lockedRequestItem->released_quantity,
                ],
            ]);

            $this->updateRequestStatus($inventoryRequest);

            return $returnRecord->fresh([
                'requestItem.item',
                'recorder',
                'activityLogs',
                'inventoryTransactions',
            ]);
        });
    }

    private function updateRequestStatus(
        InventoryRequest $inventoryRequest
    ): void {
        $inventoryRequest->load([
            'items.item',
            'items.returnRecords',
        ]);

        $hasOutstandingNonConsumables = $inventoryRequest
            ->items
            ->filter(function (RequestItem $requestItem): bool {
                return $requestItem->item->consumption_type
                    === 'non-consumable';
            })
            ->contains(function (RequestItem $requestItem): bool {
                $accounted = $requestItem->returnRecords->sum(
                    function (ReturnRecord $record): int {
                        return
                            $record->good_quantity +
                            $record->damaged_quantity +
                            $record->missing_quantity;
                    }
                );

                return $accounted < $requestItem->released_quantity;
            });

        if ($hasOutstandingNonConsumables) {
            $inventoryRequest->update([
                'status' => 'in_use',
            ]);

            return;
        }

        $inventoryRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}