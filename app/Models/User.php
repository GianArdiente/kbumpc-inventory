<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
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

    public function returnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'recorded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}