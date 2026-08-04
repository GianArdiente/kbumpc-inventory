<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'foreman_name',
        'project_name',
        'work_area',
        'status',
        'requested_at',
        'encoded_by',
        'approved_by',
        'approved_at',
        'mobilized_by',
        'mobilized_at',
        'completed_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'mobilized_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function mobilizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mobilized_by');
    }

    public function encodedInventoryRequests(): HasMany
    {
        return $this->hasMany(InventoryRequest::class, 'encoded_by');
    }

    public function approvedInventoryRequests(): HasMany
    {
        return $this->hasMany(InventoryRequest::class, 'approved_by');
    }

    public function mobilizedInventoryRequests(): HasMany
    {
        return $this->hasMany(InventoryRequest::class, 'mobilized_by');
    }
    
    public function items(): HasMany
    {
    return $this->hasMany(RequestItem::class);
    }
}