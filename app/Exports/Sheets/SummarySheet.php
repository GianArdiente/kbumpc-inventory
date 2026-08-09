<?php

namespace App\Exports\Sheets;

use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class SummarySheet implements
    FromCollection,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo
    ) {
    }

    public function title(): string
    {
        return '01 Summary';
    }

    public function collection(): Collection
    {
        $periodStart =
            $this->dateFrom . ' 00:00:00';

        $periodEnd =
            $this->dateTo . ' 23:59:59';

        $stockService =
            app(StockService::class);

        $activeItems =
            Item::query()
                ->whereNull('archived_at')
                ->get();

        $physicalUnits = 0;
        $reservedUnits = 0;
        $freeUnits = 0;

        $overallInventoryValue = 0.00;
        $physicalInventoryValue = 0.00;

        $itemsWithoutCost = 0;

        foreach ($activeItems as $item) {
            $snapshot =
                $stockService->snapshot($item);

            $physicalUnits +=
                $snapshot['physical'];

            $reservedUnits +=
                $snapshot['reserved'];

            $freeUnits +=
                $snapshot['free'];

            if ($item->unit_cost === null) {
                $itemsWithoutCost++;

                continue;
            }

            $unitCost =
                (float) $item->unit_cost;

            $overallInventoryValue +=
                $snapshot['overall']
                * $unitCost;

            $physicalInventoryValue +=
                $snapshot['physical']
                * $unitCost;
        }

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

        $pendingRequests =
            InventoryRequest::query()
                ->where(
                    'status',
                    'pending'
                )
                ->count();

        $approvedRequests =
            InventoryRequest::query()
                ->where(
                    'status',
                    'approved'
                )
                ->count();

        $mobilizedRequests =
            InventoryRequest::query()
                ->where(
                    'status',
                    'mobilized'
                )
                ->count();

        $inUseRequests =
            InventoryRequest::query()
                ->where(
                    'status',
                    'in_use'
                )
                ->count();

        $completedRequests =
            InventoryRequest::query()
                ->where(
                    'status',
                    'completed'
                )
                ->count();

        return collect([
            [
                'KBUMPC WAREHOUSE REPORT',
                null,
            ],

            [
                'Reporting Period',
                "{$this->dateFrom} to {$this->dateTo}",
            ],

            [
                null,
                null,
            ],

            [
                'CURRENT WAREHOUSE POSITION',
                null,
            ],

            [
                'Active Items',
                $activeItems->count(),
            ],

            [
                'Physical Units',
                $physicalUnits,
            ],

            [
                'Reserved Units',
                $reservedUnits,
            ],

            [
                'Free Units',
                $freeUnits,
            ],

            [
                'Overall Inventory Value',
                $overallInventoryValue,
            ],

            [
                'Physical Inventory Value',
                $physicalInventoryValue,
            ],

            [
                'Items Without Unit Cost',
                $itemsWithoutCost,
            ],

            [
                null,
                null,
            ],

            [
                'PERIOD MOVEMENT',
                null,
            ],

            [
                'Released Units',
                $releasedUnits,
            ],

            [
                'Good Returns',
                $goodReturns,
            ],

            [
                'Damaged Units',
                $damagedUnits,
            ],

            [
                'Missing Units',
                $missingUnits,
            ],

            [
                null,
                null,
            ],

            [
                'PERIOD COST & LOSS',
                null,
            ],

            [
                'Materials Consumed Value',
                $materialsConsumedValue,
            ],

            [
                'Tools Deployed Value',
                $toolsDeployedValue,
            ],

            [
                'Damaged Loss Value',
                $damagedLossValue,
            ],

            [
                'Missing Loss Value',
                $missingLossValue,
            ],

            [
                'Stock Received Value',
                $stockReceivedValue,
            ],

            [
                'Total Recorded Cost / Loss',
                $totalRecordedCost,
            ],

            [
                null,
                null,
            ],

            [
                'CURRENT REQUEST STATUS',
                null,
            ],

            [
                'Pending',
                $pendingRequests,
            ],

            [
                'Approved',
                $approvedRequests,
            ],

            [
                'Mobilized',
                $mobilizedRequests,
            ],

            [
                'In Use',
                $inUseRequests,
            ],

            [
                'Completed',
                $completedRequests,
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (
                    AfterSheet $event
                ): void {
                    $sheet =
                        $event->sheet
                            ->getDelegate();

                    $sheet->freezePane('A4');

                    $sheet->getColumnDimension('A')
                        ->setWidth(34);

                    $sheet->getColumnDimension('B')
                        ->setWidth(24);

                    $sheet->mergeCells('A1:B1');

                    $sheet->getStyle('A1:B1')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(16);

                    $sheet->getStyle('A1:B1')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    foreach ([
                        4,
                        13,
                        19,
                        27,
                    ] as $row) {
                        $sheet->mergeCells(
                            "A{$row}:B{$row}"
                        );

                        $sheet->getStyle(
                            "A{$row}:B{$row}"
                        )
                            ->getFont()
                            ->setBold(true);

                        $sheet->getStyle(
                            "A{$row}:B{$row}"
                        )
                            ->getFill()
                            ->setFillType(
                                Fill::FILL_SOLID
                            )
                            ->getStartColor()
                            ->setARGB(
                                'FFE2E8F0'
                            );
                    }

                    $sheet->getStyle(
                        'A1:B32'
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    $sheet->getStyle(
                        'B9:B10'
                    )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '₱#,##0.00'
                        );

                    $sheet->getStyle(
                        'B20:B25'
                    )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '₱#,##0.00'
                        );

                    $sheet->getStyle(
                        'A1:B32'
                    )
                        ->getAlignment()
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );

                    $sheet->getRowDimension(1)
                        ->setRowHeight(26);
                },
        ];
    }
}