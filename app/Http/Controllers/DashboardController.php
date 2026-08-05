<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\ReturnRecord;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'total_items' => Item::query()
                ->whereNull('archived_at')
                ->count(),

            'available_stock' => Item::query()
                ->whereNull('archived_at')
                ->sum('available_quantity'),

            'low_stock_items' => Item::query()
                ->whereNull('archived_at')
                ->whereColumn(
                    'available_quantity',
                    '<=',
                    'minimum_stock_level'
                )
                ->count(),

            'pending_requests' => InventoryRequest::query()
                ->where('status', 'pending')
                ->count(),

            'active_requests' => InventoryRequest::query()
                ->whereIn('status', [
                    'mobilized',
                    'in_use',
                ])
                ->count(),

            'damaged_items' => ReturnRecord::query()
                ->sum('damaged_quantity'),

            'missing_items' => ReturnRecord::query()
                ->sum('missing_quantity'),
        ];

        $recentRequests = InventoryRequest::query()
            ->with('encoder')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'statistics' => $statistics,
            'recentRequests' => $recentRequests,
        ]);
    }
}