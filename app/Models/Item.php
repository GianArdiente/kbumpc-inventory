<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Item extends Model
{
    use HasFactory;

    /**
     * Attributes that may be assigned in bulk.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_code',
        'name',
        'category',
        'consumption_type',
        'unit',
        'unit_cost',
        'overall_stock',
        'available_quantity',
        'reserved_quantity',
        'minimum_stock_level',
        'storage_location',
        'remarks',
        'archived_at',
    ];

    /**
     * Attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'archived_at' => 'datetime',
        ];
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(RequestItem::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}