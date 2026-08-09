<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\RequestItem;
use App\Models\ReturnRecord;
use App\Models\User;
use App\Services\StockService;
use DomainException;
use Illuminate\Support\Facades\DB;

class RecordItemReturn
{
    public function __construct(
        private readonly StockService $stockService
    ) {
    }

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
        ): ReturnRecord {
            $lockedRequestItem = RequestItem::query()
                ->lockForUpdate()
                ->findOrFail($requestItem->id);

            $inventoryRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail(
                    $lockedRequestItem->inventory_request_id
                );

            $item = $lockedRequestItem
                ->item()
                ->lockForUpdate()
                ->firstOrFail();

            if ($recordedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot record returns.'
                );
            }

            if (! in_array(
                $recordedBy->role,
                ['admin', 'staff'],
                true
            )) {
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

            if ($lockedRequestItem->released_quantity < 1) {
                throw new DomainException(
                    'This request item has not been released.'
                );
            }

            if (
                $goodQuantity < 0
                || $damagedQuantity < 0
                || $missingQuantity < 0
            ) {
                throw new DomainException(
                    'Return quantities cannot be negative.'
                );
            }

            $newAccountedQuantity =
                $goodQuantity
                + $damagedQuantity
                + $missingQuantity;

            if ($newAccountedQuantity < 1) {
                throw new DomainException(
                    'At least one good, damaged, or missing item must be recorded.'
                );
            }

            $previousReturns = $lockedRequestItem
                ->returnRecords()
                ->lockForUpdate()
                ->get();

            $previouslyAccounted = $previousReturns->sum(
                function (ReturnRecord $record): int {
                    return
                        $record->good_quantity
                        + $record->damaged_quantity
                        + $record->missing_quantity;
                }
            );

            $remainingToAccountFor = max(
                0,
                $lockedRequestItem->released_quantity
                - $previouslyAccounted
            );

            if ($newAccountedQuantity > $remainingToAccountFor) {
                throw new DomainException(
                    sprintf(
                        'Only %s unit(s) remain to be accounted for. You attempted to record %s.',
                        number_format($remainingToAccountFor),
                        number_format($newAccountedQuantity)
                    )
                );
            }

            $before = $this->stockService->snapshot($item);

            /*
             * Snapshot current reference cost for this return event.
             */
            $unitCostSnapshot =
                $item->unit_cost !== null
                    ? (float) $item->unit_cost
                    : null;

            $goodReturnValue =
                $unitCostSnapshot !== null
                    ? round(
                        $unitCostSnapshot * $goodQuantity,
                        2
                    )
                    : null;

            $damagedValue =
                $unitCostSnapshot !== null
                    ? round(
                        $unitCostSnapshot * $damagedQuantity,
                        2
                    )
                    : null;

            $missingValue =
                $unitCostSnapshot !== null
                    ? round(
                        $unitCostSnapshot * $missingQuantity,
                        2
                    )
                    : null;

            $newPhysical =
                $before['physical'] + $goodQuantity;

            $stockLoss =
                $damagedQuantity + $missingQuantity;

            $newOverall =
                $before['overall'] - $stockLoss;

            if (
                $newPhysical < 0
                || $newOverall < 0
                || $newPhysical > $newOverall
            ) {
                throw new DomainException(
                    "Recording this return would create an invalid stock balance for {$item->name}."
                );
            }

            $returnRecord = ReturnRecord::create([
                'request_item_id' =>
                    $lockedRequestItem->id,

                'recorded_by' =>
                    $recordedBy->id,

                'good_quantity' =>
                    $goodQuantity,

                'damaged_quantity' =>
                    $damagedQuantity,

                'missing_quantity' =>
                    $missingQuantity,

                'returned_at' =>
                    now(),

                'remarks' =>
                    $remarks,
            ]);

            $item->update([
                'available_quantity' =>
                    $newPhysical,

                'overall_stock' =>
                    $newOverall,
            ]);

            $item->refresh();

            $after =
                $this->stockService->snapshot($item);

            if ($goodQuantity > 0) {
                InventoryTransaction::create([
                    'item_id' =>
                        $item->id,

                    'user_id' =>
                        $recordedBy->id,

                    'source_type' =>
                        ReturnRecord::class,

                    'source_id' =>
                        $returnRecord->id,

                    'type' =>
                        'good_return',

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $goodReturnValue,

                    'overall_stock_change' =>
                        0,

                    'available_quantity_change' =>
                        $goodQuantity,

                    'overall_stock_after' =>
                        $after['overall'],

                    'available_quantity_after' =>
                        $after['physical'],

                    'remarks' => sprintf(
                        'Good return for request %s.',
                        $inventoryRequest->request_number
                    ),
                ]);
            }

            if ($damagedQuantity > 0) {
                InventoryTransaction::create([
                    'item_id' =>
                        $item->id,

                    'user_id' =>
                        $recordedBy->id,

                    'source_type' =>
                        ReturnRecord::class,

                    'source_id' =>
                        $returnRecord->id,

                    'type' =>
                        'damaged',

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $damagedValue,

                    'overall_stock_change' =>
                        -$damagedQuantity,

                    'available_quantity_change' =>
                        0,

                    'overall_stock_after' =>
                        $after['overall'],

                    'available_quantity_after' =>
                        $after['physical'],

                    'remarks' => sprintf(
                        'Damaged item recorded for request %s.',
                        $inventoryRequest->request_number
                    ),
                ]);
            }

            if ($missingQuantity > 0) {
                InventoryTransaction::create([
                    'item_id' =>
                        $item->id,

                    'user_id' =>
                        $recordedBy->id,

                    'source_type' =>
                        ReturnRecord::class,

                    'source_id' =>
                        $returnRecord->id,

                    'type' =>
                        'missing',

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $missingValue,

                    'overall_stock_change' =>
                        -$missingQuantity,

                    'available_quantity_change' =>
                        0,

                    'overall_stock_after' =>
                        $after['overall'],

                    'available_quantity_after' =>
                        $after['physical'],

                    'remarks' => sprintf(
                        'Missing item recorded for request %s.',
                        $inventoryRequest->request_number
                    ),
                ]);
            }

            $totalAccounted =
                $previouslyAccounted
                + $newAccountedQuantity;

            ActivityLog::create([
                'user_id' =>
                    $recordedBy->id,

                'action' =>
                    'item_return_recorded',

                'subject_type' =>
                    ReturnRecord::class,

                'subject_id' =>
                    $returnRecord->id,

                'description' => sprintf(
                    '%s recorded the return status of %s for request %s.',
                    $recordedBy->name,
                    $item->name,
                    $inventoryRequest->request_number
                ),

                'properties' => [
                    'request_id' =>
                        $inventoryRequest->id,

                    'request_number' =>
                        $inventoryRequest->request_number,

                    'request_item_id' =>
                        $lockedRequestItem->id,

                    'item_id' =>
                        $item->id,

                    'item_code' =>
                        $item->item_code,

                    'item_name' =>
                        $item->name,

                    'released_quantity' =>
                        $lockedRequestItem->released_quantity,

                    'good_quantity' =>
                        $goodQuantity,

                    'damaged_quantity' =>
                        $damagedQuantity,

                    'missing_quantity' =>
                        $missingQuantity,

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'good_return_value' =>
                        $goodReturnValue,

                    'damaged_value' =>
                        $damagedValue,

                    'missing_value' =>
                        $missingValue,

                    'previously_accounted' =>
                        $previouslyAccounted,

                    'newly_accounted' =>
                        $newAccountedQuantity,

                    'total_accounted' =>
                        $totalAccounted,

                    'remaining_unaccounted' => max(
                        0,
                        $lockedRequestItem->released_quantity
                        - $totalAccounted
                    ),

                    'stock_before' => [
                        'overall' =>
                            $before['overall'],

                        'physical' =>
                            $before['physical'],

                        'reserved' =>
                            $before['reserved'],

                        'free' =>
                            $before['free'],

                        'in_use' =>
                            $before['in_use'],
                    ],

                    'stock_after' => [
                        'overall' =>
                            $after['overall'],

                        'physical' =>
                            $after['physical'],

                        'reserved' =>
                            $after['reserved'],

                        'free' =>
                            $after['free'],

                        'in_use' =>
                            $after['in_use'],
                    ],

                    'remarks' =>
                        $remarks,
                ],
            ]);

            $this->updateRequestStatus(
                $inventoryRequest,
                $recordedBy
            );

            return $returnRecord->fresh([
                'requestItem.item',
                'recorder',
                'activityLogs',
                'inventoryTransactions',
            ]);
        });
    }

    private function updateRequestStatus(
        InventoryRequest $inventoryRequest,
        User $recordedBy
    ): void {
        $inventoryRequest->load([
            'items.item',
            'items.returnRecords',
        ]);

        $hasOutstandingNonConsumables =
            $inventoryRequest
                ->items
                ->filter(
                    function (
                        RequestItem $requestItem
                    ): bool {
                        return
                            $requestItem->item
                                ->consumption_type
                            === 'non-consumable'
                            && $requestItem
                                ->released_quantity > 0;
                    }
                )
                ->contains(
                    function (
                        RequestItem $requestItem
                    ): bool {
                        $accounted =
                            $requestItem
                                ->returnRecords
                                ->sum(
                                    function (
                                        ReturnRecord $record
                                    ): int {
                                        return
                                            $record
                                                ->good_quantity
                                            + $record
                                                ->damaged_quantity
                                            + $record
                                                ->missing_quantity;
                                    }
                                );

                        return $accounted
                            < $requestItem
                                ->released_quantity;
                    }
                );

        if ($hasOutstandingNonConsumables) {
            if ($inventoryRequest->status !== 'in_use') {
                $oldStatus =
                    $inventoryRequest->status;

                $inventoryRequest->update([
                    'status' =>
                        'in_use',
                ]);

                ActivityLog::create([
                    'user_id' =>
                        $recordedBy->id,

                    'action' =>
                        'request_in_use',

                    'subject_type' =>
                        InventoryRequest::class,

                    'subject_id' =>
                        $inventoryRequest->id,

                    'description' => sprintf(
                        'Request %s remains in use because reusable items are still outstanding.',
                        $inventoryRequest->request_number
                    ),

                    'properties' => [
                        'old_status' =>
                            $oldStatus,

                        'new_status' =>
                            'in_use',
                    ],
                ]);
            }

            return;
        }

        if ($inventoryRequest->status !== 'completed') {
            $oldStatus =
                $inventoryRequest->status;

            $inventoryRequest->update([
                'status' =>
                    'completed',

                'completed_at' =>
                    now(),
            ]);

            ActivityLog::create([
                'user_id' =>
                    $recordedBy->id,

                'action' =>
                    'request_completed',

                'subject_type' =>
                    InventoryRequest::class,

                'subject_id' =>
                    $inventoryRequest->id,

                'description' => sprintf(
                    'Request %s was completed after all reusable items were accounted for.',
                    $inventoryRequest->request_number
                ),

                'properties' => [
                    'old_status' =>
                        $oldStatus,

                    'new_status' =>
                        'completed',
                ],
            ]);
        }
    }
}