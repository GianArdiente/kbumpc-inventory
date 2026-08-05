<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use App\Models\InventoryTransaction;

class MobilizeInventoryRequest
{
    public function execute(
        InventoryRequest $inventoryRequest,
        User $mobilizedBy
    ): InventoryRequest {
        return DB::transaction(function () use ($inventoryRequest, $mobilizedBy) {
            $lockedRequest = InventoryRequest::query()
                ->lockForUpdate()
                ->findOrFail($inventoryRequest->id);

            if ($mobilizedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot mobilize requests.'
                );
            }

            if ($mobilizedBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may mobilize inventory requests.'
                );
            }

            if ($lockedRequest->status !== 'approved') {
                throw new DomainException(
                    'Only approved requests may be mobilized.'
                );
            }

            $requestItems = $lockedRequest->items()
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get();

            if ($requestItems->isEmpty()) {
                throw new DomainException(
                    'A request must contain at least one item.'
                );
            }

            $releasedItems = [];

            foreach ($requestItems as $requestItem) {
                if ($requestItem->released_quantity > 0) {
                    throw new DomainException(
                        'This request contains an item that was already released.'
                    );
                }

                $item = Item::query()
                    ->lockForUpdate()
                    ->findOrFail($requestItem->item_id);

                if ($item->archived_at !== null) {
                    throw new DomainException(
                        "{$item->name} is archived and cannot be released."
                    );
                }

                $quantity = $requestItem->requested_quantity;

                if ($item->available_quantity < $quantity) {
                    throw new DomainException(
                        "Insufficient available stock for {$item->name}."
                    );
                }

                $newAvailableQuantity =
                    $item->available_quantity - $quantity;

                $newOverallStock = $item->overall_stock;

                if ($item->consumption_type === 'consumable') {
                    $newOverallStock -= $quantity;
                }

                $item->update([
                    'available_quantity' => $newAvailableQuantity,
                    'overall_stock' => $newOverallStock,
                ]);

                $requestItem->update([
                    'released_quantity' => $quantity,
                ]);

                InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => $mobilizedBy->id,
                'source_type' => InventoryRequest::class,
                'source_id' => $lockedRequest->id,
                'type' => 'release',
                'overall_stock_change' => $item->consumption_type === 'consumable'
                    ? -$quantity
                    : 0,
                'available_quantity_change' => -$quantity,
                'overall_stock_after' => $item->overall_stock,
                'available_quantity_after' => $item->available_quantity,
                'remarks' => "Released for request {$lockedRequest->request_number}.",
                ]);

                $releasedItems[] = [
                    'item_id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'consumption_type' => $item->consumption_type,
                ];

                ActivityLog::create([
                    'user_id' => $mobilizedBy->id,
                    'action' => 'inventory_released',
                    'subject_type' => Item::class,
                    'subject_id' => $item->id,
                    'description' => sprintf(
                        '%s released %d %s of %s for request %s.',
                        $mobilizedBy->name,
                        $quantity,
                        $item->unit,
                        $item->name,
                        $lockedRequest->request_number
                    ),
                    'properties' => [
                        'request_id' => $lockedRequest->id,
                        'request_number' => $lockedRequest->request_number,
                        'released_quantity' => $quantity,
                        'available_quantity_before' =>
                            $item->available_quantity + $quantity,
                        'available_quantity_after' =>
                            $item->available_quantity,
                        'overall_stock_after' =>
                            $item->overall_stock,
                    ],
                ]);
            }

            $lockedRequest->update([
                'status' => 'mobilized',
                'mobilized_by' => $mobilizedBy->id,
                'mobilized_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $mobilizedBy->id,
                'action' => 'request_mobilized',
                'subject_type' => InventoryRequest::class,
                'subject_id' => $lockedRequest->id,
                'description' => sprintf(
                    '%s mobilized request %s.',
                    $mobilizedBy->name,
                    $lockedRequest->request_number
                ),
                'properties' => [
                    'old_status' => 'approved',
                    'new_status' => 'mobilized',
                    'released_items' => $releasedItems,
                ],
            ]);

            return $lockedRequest->fresh([
                'items.item',
                'mobilizer',
                'activityLogs',
            ]);
        });
    }
}