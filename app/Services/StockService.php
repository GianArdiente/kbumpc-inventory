<?php

namespace App\Services;

use App\Models\Item;
use App\Models\RequestItem;

class StockService
{
    /**
     * Physical quantity currently inside the warehouse.
     *
     * For non-consumables this excludes tools already mobilized.
     * For consumables this is the remaining physical quantity.
     */
    public function physical(Item $item): int
    {
        return max(
            0,
            (int) $item->available_quantity
        );
    }

    /**
     * Quantity committed to approved requests
     * but not yet mobilized.
     */
    public function reserved(Item $item): int
    {
        return max(
            0,
            (int) $item->reserved_quantity
        );
    }

    /**
     * Quantity that may still be committed to
     * another request.
     *
     * Free stock is never allowed to become negative
     * in the UI. Any negative balance is represented
     * separately as a reservation shortage.
     */
    public function free(Item $item): int
    {
        return max(
            0,
            $this->physical($item)
                - $this->reserved($item)
        );
    }

    /**
     * Reservation shortage.
     *
     * This happens when approved reservations exceed
     * the quantity physically available in the warehouse.
     *
     * Example:
     *
     * Physical = 19
     * Reserved = 20
     * Shortage = 1
     */
    public function shortage(Item $item): int
    {
        return max(
            0,
            $this->reserved($item)
                - $this->physical($item)
        );
    }

    /**
     * Determine whether approved reservations exceed
     * current physical stock.
     */
    public function isOvercommitted(Item $item): bool
    {
        return $this->shortage($item) > 0;
    }

    /**
     * Total quantity owned / recorded by the warehouse.
     *
     * Non-consumables remain part of overall stock while
     * deployed.
     *
     * Consumables decrease from overall stock when released.
     */
    public function overall(Item $item): int
    {
        return max(
            0,
            (int) $item->overall_stock
        );
    }

    /**
     * Non-consumable tools currently outside the warehouse.
     *
     * Overall stock includes deployed reusable tools while
     * physical stock does not.
     *
     * Consumables are not considered "in use" because they
     * are consumed when released.
     */
    public function inUse(Item $item): int
    {
        if ($item->consumption_type === 'consumable') {
            return 0;
        }

        return max(
            0,
            $this->overall($item)
                - $this->physical($item)
        );
    }

    /**
     * Stock status based on actual warehouse availability.
     *
     * Status priority matters:
     *
     * Archived
     * → Overcommitted
     * → Out of Stock
     * → Fully Reserved
     * → Low Stock
     * → Partially Reserved
     * → Available
     */
    public function status(Item $item): string
    {
        if ($item->archived_at !== null) {
            return 'archived';
        }

        $physical = $this->physical($item);
        $reserved = $this->reserved($item);
        $free = $this->free($item);
        $shortage = $this->shortage($item);

        /*
         * Reservation shortage takes priority because
         * warehouse staff need immediate warning when
         * more stock was promised than physically exists.
         */
        if ($shortage > 0) {
            return 'overcommitted';
        }

        if ($physical <= 0) {
            return 'out_of_stock';
        }

        /*
         * At this point Reserved cannot exceed Physical
         * because overcommitment was already caught.
         *
         * Therefore Free = 0 means the remaining physical
         * inventory is completely reserved.
         */
        if ($free <= 0 && $reserved > 0) {
            return 'fully_reserved';
        }

        if (
            $item->minimum_stock_level > 0
            && $free <= (int) $item->minimum_stock_level
        ) {
            return 'low_stock';
        }

        if ($reserved > 0) {
            return 'partially_reserved';
        }

        return 'available';
    }

    /**
     * Human-readable status for the UI.
     */
    public function statusLabel(Item $item): string
    {
        return match ($this->status($item)) {
            'archived' => 'Archived',

            'overcommitted' =>
                'Overcommitted',

            'out_of_stock' =>
                'Out of Stock',

            'fully_reserved' =>
                'Fully Reserved',

            'low_stock' =>
                'Low Stock',

            'partially_reserved' =>
                'Partially Reserved',

            default =>
                'Available',
        };
    }

    /**
     * Quantities commonly required by inventory,
     * dashboard, request, and reporting screens.
     */
    public function snapshot(Item $item): array
    {
        return [
            'overall' =>
                $this->overall($item),

            'physical' =>
                $this->physical($item),

            'reserved' =>
                $this->reserved($item),

            'free' =>
                $this->free($item),

            'shortage' =>
                $this->shortage($item),

            'overcommitted' =>
                $this->isOvercommitted($item),

            'in_use' =>
                $this->inUse($item),

            'status' =>
                $this->status($item),

            'status_label' =>
                $this->statusLabel($item),
        ];
    }

    /**
     * Calculate how much of an item is reserved
     * specifically by currently approved requests.
     *
     * This provides an audit comparison against the
     * stored reserved_quantity value.
     */
    public function calculatedReservation(Item $item): int
    {
        return (int) RequestItem::query()
            ->where('item_id', $item->id)
            ->whereHas(
                'inventoryRequest',
                fn ($query) => $query->where(
                    'status',
                    'approved'
                )
            )
            ->sum('requested_quantity');
    }

    /**
     * Check whether stored reserved_quantity agrees
     * with approved request records.
     *
     * Note:
     *
     * A reservation can be balanced while still being
     * overcommitted.
     *
     * Example:
     *
     * Stored reservation = 20
     * Approved requests   = 20
     * Physical stock      = 19
     *
     * Reservation ledger = balanced
     * Warehouse capacity = overcommitted by 1
     */
    public function reservationIsBalanced(Item $item): bool
    {
        return $this->reserved($item)
            === $this->calculatedReservation($item);
    }
}