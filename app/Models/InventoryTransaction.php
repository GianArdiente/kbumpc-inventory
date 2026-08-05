<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'user_id',
        'source_type',
        'source_id',
        'type',
        'overall_stock_change',
        'available_quantity_change',
        'overall_stock_after',
        'available_quantity_after',
        'remarks',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}