<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\RequestItem;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AddRequestItem
{
    public function execute(
        InventoryRequest $inventoryRequest,
        Item $item,
        User $addedBy,
        int $quantity,
        ?string $remarks = null
    ): RequestItem {
        return DB::transaction(function () use (
            $inventoryRequest,
            $item,
            $addedBy,
            $quantity,
            $remarks
        ): RequestItem {
            $lockedRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($inventoryRequest->id);

            $lockedItem = Item::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($addedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot add requested items.'
                );
            }

            if (! in_array(
                $addedBy->role,
                ['admin', 'staff'],
                true
            )) {
                throw new DomainException(
                    'Only Admin or Staff users may add requested items.'
                );
            }

            if ($lockedRequest->status !== 'pending') {
                throw new DomainException(
                    'Items may only be added while the request is pending.'
                );
            }

            if ($lockedItem->archived_at !== null) {
                throw new DomainException(
                    'Archived inventory items cannot be requested.'
                );
            }

            $freeToRequest = max(
                0,
                $lockedItem->available_quantity
                - $lockedItem->reserved_quantity
            );

            if ($freeToRequest < 1) {
                throw new DomainException(
                    "{$lockedItem->name} has no unreserved stock available."
                );
            }

            if ($quantity < 1) {
                throw new DomainException(
                    'Requested quantity must be at least one.'
                );
            }

            $existingRequestItem = RequestItem::query()
                ->where(
                    'inventory_request_id',
                    $lockedRequest->id
                )
                ->where('item_id', $lockedItem->id)
                ->where('request_type', 'original')
                ->lockForUpdate()
                ->first();

            $currentRequestedQuantity =
                $existingRequestItem?->requested_quantity
                ?? 0;

            $newRequestedQuantity =
                $currentRequestedQuantity + $quantity;

            if ($newRequestedQuantity > $freeToRequest) {
                throw new DomainException(
                    sprintf(
                        'Only %s %s of %s are free to request. %s are physically available, %s are reserved, and this request already contains %s.',
                        number_format($freeToRequest),
                        $lockedItem->unit,
                        $lockedItem->name,
                        number_format(
                            $lockedItem->available_quantity
                        ),
                        number_format(
                            $lockedItem->reserved_quantity
                        ),
                        number_format(
                            $currentRequestedQuantity
                        )
                    )
                );
            }

            if ($existingRequestItem) {
                $existingRequestItem->update([
                    'requested_quantity' =>
                        $newRequestedQuantity,

                    'remarks' => filled($remarks)
                        ? $remarks
                        : $existingRequestItem->remarks,
                ]);

                $requestItem =
                    $existingRequestItem->fresh();

                $action =
                    'request_item_quantity_increased';
            } else {
                $requestItem = RequestItem::create([
                    'inventory_request_id' =>
                        $lockedRequest->id,

                    'item_id' => $lockedItem->id,
                    'request_type' => 'original',
                    'requested_quantity' => $quantity,
                    'released_quantity' => 0,
                    'remarks' => $remarks,
                ]);

                $action = 'request_item_added';
            }

            ActivityLog::create([
                'user_id' => $addedBy->id,
                'action' => $action,

                'subject_type' =>
                    InventoryRequest::class,

                'subject_id' => $lockedRequest->id,

                'description' => sprintf(
                    '%s added %s %s of %s to request %s.',
                    $addedBy->name,
                    number_format($quantity),
                    $lockedItem->unit,
                    $lockedItem->name,
                    $lockedRequest->request_number
                ),

                'properties' => [
                    'request_item_id' =>
                        $requestItem->id,

                    'item_id' => $lockedItem->id,
                    'item_code' =>
                        $lockedItem->item_code,

                    'item_name' => $lockedItem->name,

                    'added_quantity' => $quantity,

                    'previous_requested_quantity' =>
                        $currentRequestedQuantity,

                    'total_requested_quantity' =>
                        $requestItem->requested_quantity,

                    'available_quantity' =>
                        $lockedItem->available_quantity,

                    'reserved_quantity' =>
                        $lockedItem->reserved_quantity,

                    'free_to_request' => $freeToRequest,

                    'request_type' => 'original',
                ],
            ]);

            return $requestItem->fresh('item');
        });
    }
}