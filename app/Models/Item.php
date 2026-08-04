<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'overall_stock',
        'available_quantity',
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
            'archived_at' => 'datetime',
        ];
    }

    public function requestItems(): HasMany
    {
    return $this->hasMany(RequestItem::class);
    }
}