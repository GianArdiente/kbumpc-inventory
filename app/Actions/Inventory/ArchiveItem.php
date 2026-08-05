<?php

namespace App\Actions\Inventory;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ArchiveItem
{
    public function execute(
        Item $item,
        User $archivedBy
    ): Item {
        return DB::transaction(function () use (
            $item,
            $archivedBy
        ) {
            $lockedItem = Item::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            if ($archivedBy->status !== 'active') {
                throw new DomainException(
                    'Inactive users cannot archive inventory items.'
                );
            }

            if ($archivedBy->role !== 'admin') {
                throw new DomainException(
                    'Only an Admin may archive inventory items.'
                );
            }

            if ($lockedItem->archived_at !== null) {
                throw new DomainException(
                    'This item has already been archived.'
                );
            }

            if (
                $lockedItem->available_quantity !==
                $lockedItem->overall_stock
            ) {
                throw new DomainException(
                    'This item cannot be archived because some units are still deployed.'
                );
            }

            $lockedItem->update([
                'archived_at' => now(),
            ]);

            ActivityLog::create([
                'user_id' => $archivedBy->id,
                'action' => 'item_archived',
                'subject_type' => Item::class,
                'subject_id' => $lockedItem->id,
                'description' => sprintf(
                    '%s archived %s.',
                    $archivedBy->name,
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