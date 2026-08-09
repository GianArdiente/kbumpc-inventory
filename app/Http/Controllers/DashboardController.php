<?php

namespace App\Http\Controllers;

use App\Models\InventoryRequest;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Services\StockService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        StockService $stockService
    ): View {
        $statistics = [
            'total_items' => Item::query()
                ->whereNull('archived_at')
                ->count(),

            'physical_units' => 0,
            'reserved_units' => 0,
            'free_units' => 0,

            'low_stock_items' => 0,
            'fully_reserved_items' => 0,
            'out_of_stock_items' => 0,

            'pending_requests' => InventoryRequest::query()
                ->where('status', 'pending')
                ->count(),

            'approved_requests' => InventoryRequest::query()
                ->where('status', 'approved')
                ->count(),

            'active_requests' => InventoryRequest::query()
                ->whereIn('status', [
                    'approved',
                    'mobilized',
                    'in_use',
                ])
                ->count(),

            'completed_requests' => InventoryRequest::query()
                ->where('status', 'completed')
                ->count(),

            'damaged_items' => ReturnRecord::query()
                ->sum('damaged_quantity'),

            'missing_items' => ReturnRecord::query()
                ->sum('missing_quantity'),
        ];

        /*
         * Process inventory in chunks so the dashboard remains
         * memory-friendly even when the warehouse grows to
         * thousands of inventory records.
         */
        Item::query()
            ->whereNull('archived_at')
            ->select([
                'id',
                'overall_stock',
                'available_quantity',
                'reserved_quantity',
                'minimum_stock_level',
                'consumption_type',
                'archived_at',
            ])
            ->chunkById(
                500,
                function ($items) use (
                    $stockService,
                    &$statistics
                ): void {
                    foreach ($items as $item) {
                        $snapshot =
                            $stockService->snapshot($item);

                        $statistics['physical_units'] +=
                            $snapshot['physical'];

                        $statistics['reserved_units'] +=
                            $snapshot['reserved'];

                        $statistics['free_units'] +=
                            $snapshot['free'];

                        if (
                            $snapshot['status']
                            === 'low_stock'
                        ) {
                            $statistics['low_stock_items']++;
                        }

                        if (
                            $snapshot['status']
                            === 'fully_reserved'
                        ) {
                            $statistics['fully_reserved_items']++;
                        }

                        if (
                            $snapshot['status']
                            === 'out_of_stock'
                        ) {
                            $statistics['out_of_stock_items']++;
                        }
                    }
                }
            );

        $recentRequests = InventoryRequest::query()
            ->with('encoder')
            ->latest('requested_at')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'statistics' => $statistics,
            'recentRequests' => $recentRequests,
        ]);
    }
}