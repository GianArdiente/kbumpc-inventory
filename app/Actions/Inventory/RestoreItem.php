<?php

namespace App\Actions\Inventory;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class RestoreItem
{
    public function execute(
        Item $item,
        User $restoredBy
    ): Item {
        return DB::transaction(function () use (
            $item,
            $restoredBy
        ) {
            $lockedItem = Item::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($restoredBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot restore inventory items.'
                );
            }

            if ($restoredBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may restore inventory items.'
                );
            }

            if ($lockedItem->archived_at === null) {
                throw new DomainException(
                    'This item is not archived.'
                );
            }

            $lockedItem->update([
                'archived_at' => null,
            ]);

            ActivityLog::create([
                'user_id' => $restoredBy->id,
                'action' => 'item_restored',
                'subject_type' => Item::class,
                'subject_id' => $lockedItem->id,
                'description' => sprintf(
                    '%s restored %s.',
                    $restoredBy->name,
                    $lockedItem->name
                ),
                'properties' => [
                    'item_code' => $lockedItem->item_code,
                    'item_name' => $lockedItem->name,
                ],
            ]);

            return $lockedItem->fresh();
        });
    }
}