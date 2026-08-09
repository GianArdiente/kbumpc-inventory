<?php

namespace App\Actions\Inventory;

use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class AdjustItemStock
{
    public function execute(
        Item $item,
        User $adjustedBy,
        int $overallChange,
        int $availableChange,
        string $type,
        string $remarks
    ): Item {
        return DB::transaction(function () use (
            $item,
            $adjustedBy,
            $overallChange,
            $availableChange,
            $type,
            $remarks
        ): Item {
            $lockedItem = Item::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($adjustedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot adjust inventory.'
                );
            }

            if ($adjustedBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may adjust inventory.'
                );
            }

            if ($lockedItem->archived_at !== null) {
                throw new DomainException(
                    'Archived items cannot be adjusted.'
                );
            }

            if (! in_array(
                $type,
                [
                    'stock_in',
                    'correction',
                ],
                true
            )) {
                throw new DomainException(
                    'Invalid inventory adjustment type.'
                );
            }

            if (
                $overallChange === 0
                && $availableChange === 0
            ) {
                throw new DomainException(
                    'At least one stock value must change.'
                );
            }

            $newOverallStock =
                $lockedItem->overall_stock
                + $overallChange;

            $newAvailableQuantity =
                $lockedItem->available_quantity
                + $availableChange;

            if ($newOverallStock < 0) {
                throw new DomainException(
                    'Overall stock cannot become negative.'
                );
            }

            if ($newAvailableQuantity < 0) {
                throw new DomainException(
                    'Available quantity cannot become negative.'
                );
            }

            if (
                $newAvailableQuantity >
                $newOverallStock
            ) {
                throw new DomainException(
                    'Available quantity cannot exceed overall stock.'
                );
            }

            $before = [
                'overall_stock' =>
                    $lockedItem->overall_stock,

                'available_quantity' =>
                    $lockedItem->available_quantity,
            ];

            /*
             * Snapshot the current reference cost before
             * the stock movement is recorded.
             *
             * Historical transactions must never depend
             * on future item price changes.
             */
            $unitCostSnapshot =
                $lockedItem->unit_cost !== null
                    ? (float) $lockedItem->unit_cost
                    : null;

            /*
             * Financial movement is based on overall stock
             * movement, because overall_stock represents
             * actual company-owned inventory.
             *
             * Example:
             *
             * Stock In +5 at ₱125.50
             * total_cost = ₱627.50
             *
             * Correction -2 at ₱125.50
             * total_cost = ₱251.00
             *
             * The direction is still represented by
             * overall_stock_change.
             */
            $costQuantity = abs($overallChange);

            $totalCost =
                $unitCostSnapshot !== null
                && $costQuantity > 0
                    ? round(
                        $unitCostSnapshot
                        * $costQuantity,
                        2
                    )
                    : null;

            $lockedItem->update([
                'overall_stock' =>
                    $newOverallStock,

                'available_quantity' =>
                    $newAvailableQuantity,
            ]);

            InventoryTransaction::create([
                'item_id' =>
                    $lockedItem->id,

                'user_id' =>
                    $adjustedBy->id,

                'source_type' =>
                    Item::class,

                'source_id' =>
                    $lockedItem->id,

                'type' =>
                    $type,

                'unit_cost_snapshot' =>
                    $unitCostSnapshot,

                'total_cost' =>
                    $totalCost,

                'overall_stock_change' =>
                    $overallChange,

                'available_quantity_change' =>
                    $availableChange,

                'overall_stock_after' =>
                    $newOverallStock,

                'available_quantity_after' =>
                    $newAvailableQuantity,

                'remarks' =>
                    $remarks,
            ]);

            ActivityLog::create([
                'user_id' =>
                    $adjustedBy->id,

                'action' =>
                    'inventory_adjusted',

                'subject_type' =>
                    Item::class,

                'subject_id' =>
                    $lockedItem->id,

                'description' => sprintf(
                    '%s adjusted inventory for %s.',
                    $adjustedBy->name,
                    $lockedItem->name
                ),

                'properties' => [
                    'type' =>
                        $type,

                    'overall_stock_before' =>
                        $before['overall_stock'],

                    'overall_stock_after' =>
                        $newOverallStock,

                    'available_quantity_before' =>
                        $before['available_quantity'],

                    'available_quantity_after' =>
                        $newAvailableQuantity,

                    'unit_cost_snapshot' =>
                        $unitCostSnapshot,

                    'total_cost' =>
                        $totalCost,

                    'remarks' =>
                        $remarks,
                ],
            ]);

            return $lockedItem->fresh([
                'inventoryTransactions',
                'activityLogs',
            ]);
        });
    }
}