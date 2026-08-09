<?php

namespace App\Http\Controllers;

use App\Exports\WarehouseReportExport;
use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\ReturnRecord;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(
        Request $request,
        StockService $stockService
    ): View {
        $validated = $request->validate([
            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $dateFrom =
            $validated['date_from']
            ?? now()
                ->startOfMonth()
                ->toDateString();

        $dateTo =
            $validated['date_to']
            ?? now()
                ->endOfMonth()
                ->toDateString();

        $periodStart =
            $dateFrom . ' 00:00:00';

        $periodEnd =
            $dateTo . ' 23:59:59';

        $inventorySummary = [
            'active_items' => 0,

            'physical_units' => 0,
            'reserved_units' => 0,
            'free_units' => 0,

            'low_stock_items' => 0,
            'out_of_stock_items' => 0,
            'fully_reserved_items' => 0,
            'overcommitted_items' => 0,

            'overall_inventory_value' => 0.00,
            'physical_inventory_value' => 0.00,
            'reserved_inventory_value' => 0.00,
            'free_inventory_value' => 0.00,

            'items_without_cost' => 0,
        ];

        Item::query()
            ->whereNull('archived_at')
            ->chunkById(
                500,
                function ($items) use (
                    $stockService,
                    &$inventorySummary
                ): void {
                    foreach ($items as $item) {
                        $snapshot =
                            $stockService->snapshot($item);

                        $inventorySummary['active_items']++;

                        $inventorySummary['physical_units'] +=
                            $snapshot['physical'];

                        $inventorySummary['reserved_units'] +=
                            $snapshot['reserved'];

                        $inventorySummary['free_units'] +=
                            $snapshot['free'];

                        if (
                            $snapshot['status']
                            === 'low_stock'
                        ) {
                            $inventorySummary[
                                'low_stock_items'
                            ]++;
                        }

                        if (
                            $snapshot['status']
                            === 'out_of_stock'
                        ) {
                            $inventorySummary[
                                'out_of_stock_items'
                            ]++;
                        }

                        if (
                            $snapshot['status']
                            === 'fully_reserved'
                        ) {
                            $inventorySummary[
                                'fully_reserved_items'
                            ]++;
                        }

                        if (
                            $snapshot['status']
                            === 'overcommitted'
                        ) {
                            $inventorySummary[
                                'overcommitted_items'
                            ]++;
                        }

                        if ($item->unit_cost === null) {
                            $inventorySummary[
                                'items_without_cost'
                            ]++;

                            continue;
                        }

                        $unitCost =
                            (float) $item->unit_cost;

                        $inventorySummary[
                            'overall_inventory_value'
                        ] +=
                            $snapshot['overall']
                            * $unitCost;

                        $inventorySummary[
                            'physical_inventory_value'
                        ] +=
                            $snapshot['physical']
                            * $unitCost;

                        $inventorySummary[
                            'reserved_inventory_value'
                        ] +=
                            $snapshot['reserved']
                            * $unitCost;

                        $inventorySummary[
                            'free_inventory_value'
                        ] +=
                            $snapshot['free']
                            * $unitCost;
                    }
                }
            );

        $requestSummary = [
            'pending' =>
                InventoryRequest::query()
                    ->where(
                        'status',
                        'pending'
                    )
                    ->count(),

            'approved' =>
                InventoryRequest::query()
                    ->where(
                        'status',
                        'approved'
                    )
                    ->count(),

            'mobilized' =>
                InventoryRequest::query()
                    ->where(
                        'status',
                        'mobilized'
                    )
                    ->count(),

            'in_use' =>
                InventoryRequest::query()
                    ->where(
                        'status',
                        'in_use'
                    )
                    ->count(),

            'completed' =>
                InventoryRequest::query()
                    ->where(
                        'status',
                        'completed'
                    )
                    ->count(),
        ];

        $transactionQuery =
            InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                );

        $releasedUnits =
            abs(
                (int) (clone $transactionQuery)
                    ->where(
                        'type',
                        'release'
                    )
                    ->sum(
                        'available_quantity_change'
                    )
            );

        $goodReturns =
            (int) (clone $transactionQuery)
                ->where(
                    'type',
                    'good_return'
                )
                ->sum(
                    'available_quantity_change'
                );

        $damagedUnits =
            abs(
                (int) (clone $transactionQuery)
                    ->where(
                        'type',
                        'damaged'
                    )
                    ->sum(
                        'overall_stock_change'
                    )
            );

        $missingUnits =
            abs(
                (int) (clone $transactionQuery)
                    ->where(
                        'type',
                        'missing'
                    )
                    ->sum(
                        'overall_stock_change'
                    )
            );

        $materialsConsumedValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'release'
                )
                ->whereHas(
                    'item',
                    fn ($query) =>
                        $query->where(
                            'consumption_type',
                            'consumable'
                        )
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $toolsDeployedValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'release'
                )
                ->whereHas(
                    'item',
                    fn ($query) =>
                        $query->where(
                            'consumption_type',
                            'non-consumable'
                        )
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $goodReturnValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'good_return'
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $damagedLossValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'damaged'
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $missingLossValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'missing'
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $stockReceivedValue =
            (float) InventoryTransaction::query()
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->where(
                    'type',
                    'stock_in'
                )
                ->whereNotNull(
                    'total_cost'
                )
                ->sum(
                    'total_cost'
                );

        $totalRecordedCost =
            $materialsConsumedValue
            + $damagedLossValue
            + $missingLossValue;

        $usageSummary = [
            'released_units' =>
                $releasedUnits,

            'good_returns' =>
                $goodReturns,

            'damaged_units' =>
                $damagedUnits,

            'missing_units' =>
                $missingUnits,

            'materials_consumed_value' =>
                $materialsConsumedValue,

            'tools_deployed_value' =>
                $toolsDeployedValue,

            'good_return_value' =>
                $goodReturnValue,

            'damaged_loss_value' =>
                $damagedLossValue,

            'missing_loss_value' =>
                $missingLossValue,

            'stock_received_value' =>
                $stockReceivedValue,

            'total_recorded_cost' =>
                $totalRecordedCost,
        ];

        $costCoverage = [
            'transactions_with_cost' =>
                InventoryTransaction::query()
                    ->whereBetween(
                        'created_at',
                        [
                            $periodStart,
                            $periodEnd,
                        ]
                    )
                    ->whereNotNull(
                        'unit_cost_snapshot'
                    )
                    ->count(),

            'transactions_without_cost' =>
                InventoryTransaction::query()
                    ->whereBetween(
                        'created_at',
                        [
                            $periodStart,
                            $periodEnd,
                        ]
                    )
                    ->whereNull(
                        'unit_cost_snapshot'
                    )
                    ->count(),
        ];

        $recentTransactions =
            InventoryTransaction::query()
                ->with([
                    'item',
                    'user',
                ])
                ->whereBetween(
                    'created_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->latest()
                ->limit(50)
                ->get();

        $recentCompletedRequests =
            InventoryRequest::query()
                ->with([
                    'encoder',
                    'items.item',
                ])
                ->where(
                    'status',
                    'completed'
                )
                ->whereBetween(
                    'completed_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->latest(
                    'completed_at'
                )
                ->limit(50)
                ->get();

        $recentReturns =
            ReturnRecord::query()
                ->with([
                    'requestItem.item',
                    'requestItem.inventoryRequest',
                    'recorder',
                ])
                ->whereBetween(
                    'returned_at',
                    [
                        $periodStart,
                        $periodEnd,
                    ]
                )
                ->latest(
                    'returned_at'
                )
                ->limit(50)
                ->get();

        return view(
            'reports.index',
            [
                'dateFrom' =>
                    $dateFrom,

                'dateTo' =>
                    $dateTo,

                'inventorySummary' =>
                    $inventorySummary,

                'requestSummary' =>
                    $requestSummary,

                'usageSummary' =>
                    $usageSummary,

                'costCoverage' =>
                    $costCoverage,

                'recentTransactions' =>
                    $recentTransactions,

                'recentCompletedRequests' =>
                    $recentCompletedRequests,

                'recentReturns' =>
                    $recentReturns,
            ]
        );
    }

    public function export(
        Request $request
    ): BinaryFileResponse {
        $validated = $request->validate([
            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        $dateFrom =
            $validated['date_from']
            ?? now()
                ->startOfMonth()
                ->toDateString();

        $dateTo =
            $validated['date_to']
            ?? now()
                ->endOfMonth()
                ->toDateString();

        $fileName = sprintf(
            'KBUMPC_Warehouse_Report_%s_to_%s.xlsx',
            $dateFrom,
            $dateTo
        );

        return Excel::download(
            new WarehouseReportExport(
                $dateFrom,
                $dateTo
            ),
            $fileName
        );
    }
}