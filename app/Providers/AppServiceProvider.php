<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\RequestItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $sidebarNotifications = [
                'inventory' => 0,
                'requests' => 0,
                'returns' => 0,
                'activity_logs' => 0,
                'users' => 0,
            ];

            /*
            |--------------------------------------------------------------------------
            | Inventory
            |--------------------------------------------------------------------------
            |
            | Active items that have reached or fallen below
            | their minimum stock level.
            |
            */

            if (Schema::hasTable('items')) {
                $sidebarNotifications['inventory'] =
                    Item::query()
                        ->whereNull('archived_at')
                        ->whereColumn(
                            'available_quantity',
                            '<=',
                            'minimum_stock_level'
                        )
                        ->count();
            }

            /*
            |--------------------------------------------------------------------------
            | Requests
            |--------------------------------------------------------------------------
            |
            | Requests waiting for warehouse action.
            |
            */

            if (Schema::hasTable('inventory_requests')) {
                $sidebarNotifications['requests'] =
                    InventoryRequest::query()
                        ->where('status', 'pending')
                        ->count();
            }

            /*
            |--------------------------------------------------------------------------
            | Returns
            |--------------------------------------------------------------------------
            |
            | Released non-consumable request items that have
            | not yet been fully accounted for.
            |
            */

            if (
                Schema::hasTable('request_items')
                && Schema::hasTable('return_records')
                && Schema::hasTable('inventory_requests')
                && Schema::hasTable('items')
            ) {
                $sidebarNotifications['returns'] =
                    RequestItem::query()
                        ->where('released_quantity', '>', 0)

                        ->whereHas(
                            'inventoryRequest',
                            function ($query): void {
                                $query->whereIn(
                                    'status',
                                    [
                                        'mobilized',
                                        'in_use',
                                    ]
                                );
                            }
                        )

                        ->whereHas(
                            'item',
                            function ($query): void {
                                $query->where(
                                    'consumption_type',
                                    'non-consumable'
                                );
                            }
                        )

                        ->whereRaw(
                            '
                                request_items.released_quantity >
                                COALESCE(
                                    (
                                        SELECT SUM(
                                            return_records.good_quantity
                                            + return_records.damaged_quantity
                                            + return_records.missing_quantity
                                        )
                                        FROM return_records
                                        WHERE return_records.request_item_id
                                            = request_items.id
                                    ),
                                    0
                                )
                            '
                        )

                        ->count();
            }

            /*
            |--------------------------------------------------------------------------
            | Admin Notifications
            |--------------------------------------------------------------------------
            */

            if (
                Auth::check()
                && Auth::user()->role === 'admin'
                && Auth::user()->status === 'active'
            ) {
                /*
                |--------------------------------------------------------------------------
                | Activity Logs
                |--------------------------------------------------------------------------
                */

                if (
                    Schema::hasTable('activity_logs')
                    && Schema::hasColumn(
                        'users',
                        'activity_logs_last_viewed_at'
                    )
                ) {
                    $lastViewedAt =
                        Auth::user()
                            ->activity_logs_last_viewed_at;

                    $sidebarNotifications['activity_logs'] =
                        ActivityLog::query()
                            ->when(
                                $lastViewedAt,
                                function ($query) use (
                                    $lastViewedAt
                                ): void {
                                    $query->where(
                                        'created_at',
                                        '>',
                                        $lastViewedAt
                                    );
                                }
                            )
                            ->count();
                }

                /*
                |--------------------------------------------------------------------------
                | Users
                |--------------------------------------------------------------------------
                |
                | Inactive accounts that an administrator may
                | want to review or reactivate.
                |
                */

                if (Schema::hasTable('users')) {
                    $sidebarNotifications['users'] =
                        User::query()
                            ->where(
                                'status',
                                'inactive'
                            )
                            ->count();
                }
            }

            $view->with(
                'sidebarNotifications',
                $sidebarNotifications
            );
        });
    }
}