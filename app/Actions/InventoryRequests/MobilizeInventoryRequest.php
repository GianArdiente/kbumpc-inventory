<?php

namespace App\Actions\InventoryRequests;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use App\Services\StockService;
use DomainException;
use Illuminate\Support\Facades\DB;

class MobilizeInventoryRequest
{
    public function __construct(
        private readonly StockService $stockService
    ) {
    }

    public function execute(
        InventoryRequest $inventoryRequest,
        User $mobilizedBy
    ): InventoryRequest {
        return DB::transaction(function () use (
            $inventoryRequest,
            $mobilizedBy
        ): InventoryRequest {
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

            $requestItems = $lockedRequest
                ->items()
                ->orderBy('item_id')
                ->lockForUpdate()
                ->get();

            if ($requestItems->isEmpty()) {
                throw new DomainException(
                    'A request must contain at least one item before mobilization.'
                );
            }

            $releasedItems = [];

            foreach ($requestItems as $requestItem) {
                if ($requestItem->requested_quantity < 1) {
                    throw new DomainException(
                        'The request contains an invalid requested quantity.'
                    );
                }

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

                $quantity =
                    (int) $requestItem->requested_quantity;

                $before =
                    $this->stockService->snapshot($item);

                if ($before['reserved'] < $quantity) {
                    throw new DomainException(
                        sprintf(
                            '%s cannot be mobilized. The request requires %s %s, but only %s %s are currently reserved.',
                            $item->name,
                            number_format($quantity),
                            $item->unit,
                            number_format($before['reserved']),
                            $item->unit
                        )
                    );
                }

                if ($before['physical'] < $quantity) {
                    throw new DomainException(
                        sprintf(
                            '%s cannot be mobilized. Only %s %s are physically inside the warehouse.',
                            $item->name,
                            number_format($before['physical']),
                            $item->unit
                        )
                    );
                }

                /*
                 * Snapshot cost BEFORE release.
                 *
                 * This is the historical value of the item
                 * at the exact time it leaves the warehouse.
                 */
                $unitCostSnapshot =
                    $item->unit_cost !== null
                        ? (float) $item->unit_cost
                        : null;

                $totalCost =
                    $unitCostSnapshot !== null
                        ? round(
                            $unitCostSnapshot * $quantity,
                            2
                        )
                        : null;

                $newPhysical =
                    $before['physical'] - $quantity;

                $newReserved =
                    $before['reserved'] - $quantity;

                $newOverall =
                    $before['overall'];

                if (
                    $item->consumption_type === 'consumable'
                ) {
                    $newOverall -= $quantity;
                }

                if (
                    $newPhysical < 0
                    || $newReserved < 0
                    || $newOverall < 0
                ) {
                    throw new DomainException(
                        "Mobilizing {$item->name} would create an invalid stock balance."
                    );
                }

                $item->update([
                    'available_quantity' =>
                        $newPhysical,

                    'reserved_quantity' =>
                        $newReserved,

                    'overall_stock' =>
                        $newOverall,
                ]);

                $requestItem->update([
                    'released_quantity' =>
                        $quantity,
                ]);

                $item->refresh();

                $after =
                    $this->stockService->snapshot($item);

                InventoryTransaction::create([
                    'item_id' =>
                        $item->id,

                    'user_id' =>
                        $mobilizedBy->id,

                    'source_type' =>
                        InventoryRequest::class,

                    'source_id' =>
                        $lockedRequest->id,

                    'type' =>
                        'release',

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $totalCost,

                    'overall_stock_change' =>
                        $item->consumption_type === 'consumable'
                            ? -$quantity
                            : 0,

                    'available_quantity_change' =>
                        -$quantity,

                    'overall_stock_after' =>
                        $after['overall'],

                    'available_quantity_after' =>
                        $after['physical'],

                    'remarks' => sprintf(
                        'Released for request %s.',
                        $lockedRequest->request_number
                    ),
                ]);

                $releasedItems[] = [
                    'request_item_id' =>
                        $requestItem->id,

                    'item_id' =>
                        $item->id,

                    'item_code' =>
                        $item->item_code,

                    'item_name' =>
                        $item->name,

                    'quantity' =>
                        $quantity,

                    'unit' =>
                        $item->unit,

                    'consumption_type' =>
                        $item->consumption_type,

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $totalCost,

                    'before' => [
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

                    'after' => [
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
                ];

                ActivityLog::create([
                    'user_id' =>
                        $mobilizedBy->id,

                    'action' =>
                        'inventory_released',

                    'subject_type' =>
                        Item::class,

                    'subject_id' =>
                        $item->id,

                    'description' => sprintf(
                        '%s released %s %s of %s for request %s.',
                        $mobilizedBy->name,
                        number_format($quantity),
                        $item->unit,
                        $item->name,
                        $lockedRequest->request_number
                    ),

                    'properties' => [
                        'request_id' =>
                            $lockedRequest->id,

                        'request_number' =>
                            $lockedRequest->request_number,

                        'request_item_id' =>
                            $requestItem->id,

                        'released_quantity' =>
                            $quantity,

                        'unit_cost_snapshot' =>
                            $unitCostSnapshot,

                        'total_cost' =>
                            $totalCost,

                        'overall_before' =>
                            $before['overall'],

                        'overall_after' =>
                            $after['overall'],

                        'physical_before' =>
                            $before['physical'],

                        'physical_after' =>
                            $after['physical'],

                        'reserved_before' =>
                            $before['reserved'],

                        'reserved_after' =>
                            $after['reserved'],

                        'free_before' =>
                            $before['free'],

                        'free_after' =>
                            $after['free'],

                        'in_use_before' =>
                            $before['in_use'],

                        'in_use_after' =>
                            $after['in_use'],
                    ],
                ]);
            }

            $lockedRequest->update([
                'status' =>
                    'mobilized',

                'mobilized_by' =>
                    $mobilizedBy->id,

                'mobilized_at' =>
                    now(),
            ]);

            ActivityLog::create([
                'user_id' =>
                    $mobilizedBy->id,

                'action' =>
                    'request_mobilized',

                'subject_type' =>
                    InventoryRequest::class,

                'subject_id' =>
                    $lockedRequest->id,

                'description' => sprintf(
                    '%s mobilized request %s and released %s inventory line(s).',
                    $mobilizedBy->name,
                    $lockedRequest->request_number,
                    number_format(
                        count($releasedItems)
                    )
                ),

                'properties' => [
                    'old_status' =>
                        'approved',

                    'new_status' =>
                        'mobilized',

                    'released_items' =>
                        $releasedItems,
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