<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ApproveInventoryRequest
{
    public function execute(
        InventoryRequest $inventoryRequest,
        User $approvedBy
    ): InventoryRequest {
        return DB::transaction(function () use (
            $inventoryRequest,
            $approvedBy
        ): InventoryRequest {
            $lockedRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($inventoryRequest->id);

            if ($approvedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot approve requests.'
                );
            }

            if ($approvedBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may approve inventory requests.'
                );
            }

            if ($lockedRequest->status !== 'pending') {
                throw new DomainException(
                    'Only pending requests may be approved.'
                );
            }

            $requestItems = $lockedRequest
                ->items()
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get();

            if ($requestItems->isEmpty()) {
                throw new DomainException(
                    'A request must contain at least one item before approval.'
                );
            }

            $reservedItems = [];

            foreach ($requestItems as $requestItem) {
                $item = Item::query()
                    ->lockForUpdate()
                    ->findOrFail($requestItem->item_id);

                if ($item->archived_at !== null) {
                    throw new DomainException(
                        "{$item->name} is archived and cannot be reserved."
                    );
                }

                $freeToRequest = max(
                    0,
                    $item->available_quantity
                    - $item->reserved_quantity
                );

                if (
                    $requestItem->requested_quantity >
                    $freeToRequest
                ) {
                    throw new DomainException(
                        sprintf(
                            '%s cannot be approved. Only %s %s are free to request; %s are already reserved.',
                            $item->name,
                            number_format($freeToRequest),
                            $item->unit,
                            number_format($item->reserved_quantity)
                        )
                    );
                }

                $reservedBefore = $item->reserved_quantity;

                $item->increment(
                    'reserved_quantity',
                    $requestItem->requested_quantity
                );

                $item->refresh();

                $reservedItems[] = [
                    'item_id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->name,
                    'reserved_quantity' =>
                        $requestItem->requested_quantity,
                    'reserved_before' => $reservedBefore,
                    'reserved_after' =>
                        $item->reserved_quantity,
                    'available_quantity' =>
                        $item->available_quantity,
                    'free_to_request_after' => max(
                        0,
                        $item->available_quantity
                        - $item->reserved_quantity
                    ),
                ];
            }

            $oldStatus = $lockedRequest->status;

            $lockedRequest->update([
                'status' => 'approved',
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $approvedBy->id,
                'action' => 'request_approved',
                'subject_type' => InventoryRequest::class,
                'subject_id' => $lockedRequest->id,
                'description' => sprintf(
                    '%s approved request %s and reserved its inventory.',
                    $approvedBy->name,
                    $lockedRequest->request_number
                ),
                'properties' => [
                    'old_status' => $oldStatus,
                    'new_status' => 'approved',
                    'reserved_items' => $reservedItems,
                ],
            ]);

            return $lockedRequest->fresh([
                'items.item',
                'approver',
                'activityLogs',
            ]);
        });
    }
}