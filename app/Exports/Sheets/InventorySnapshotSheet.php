<?php

namespace App\Exports\Sheets;

use App\Models\Item;
use App\Services\StockService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventorySnapshotSheet implements
    FromCollection,
    WithHeadings,
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
        return '10 Inventory Snapshot';
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item',
            'Category',
            'Consumption Type',
            'Unit',
            'Overall Stock',
            'Physical',
            'Reserved',
            'Free',
            'Deployed / In Use',
            'Minimum Stock',
            'Stock Status',
            'Unit Cost',
            'Overall Value',
            'Physical Value',
            'Reserved Value',
            'Free Value',
            'Storage Location',
            'Remarks',
        ];
    }

    public function collection(): Collection
    {
        $stockService =
            app(StockService::class);

        return Item::query()
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get()
            ->map(
                function (Item $item) use (
                    $stockService
                ): array {
                    $snapshot =
                        $stockService->snapshot($item);

                    $unitCost =
                        $item->unit_cost !== null
                            ? (float) $item->unit_cost
                            : null;

                    $overallValue =
                        $unitCost !== null
                            ? round(
                                $snapshot['overall']
                                * $unitCost,
                                2
                            )
                            : null;

                    $physicalValue =
                        $unitCost !== null
                            ? round(
                                $snapshot['physical']
                                * $unitCost,
                                2
                            )
                            : null;

                    $reservedValue =
                        $unitCost !== null
                            ? round(
                                $snapshot['reserved']
                                * $unitCost,
                                2
                            )
                            : null;

                    $freeValue =
                        $unitCost !== null
                            ? round(
                                $snapshot['free']
                                * $unitCost,
                                2
                            )
                            : null;

                    return [
                        $item->item_code,

                        $item->name,

                        ucfirst(
                            $item->category
                        ),

                        ucfirst(
                            str_replace(
                                '-',
                                ' ',
                                $item->consumption_type
                            )
                        ),

                        $item->unit,

                        $snapshot['overall'],

                        $snapshot['physical'],

                        $snapshot['reserved'],

                        $snapshot['free'],

                        $snapshot['in_use'],

                        $item->minimum_stock_level,

                        $snapshot['status_label'],

                        $unitCost,

                        $overallValue,

                        $physicalValue,

                        $reservedValue,

                        $freeValue,

                        $item->storage_location
                            ?? 'Not assigned',

                        $item->remarks
                            ?? '',
                    ];
                }
            );
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

                    $sheet->freezePane('A2');

                    $sheet->setAutoFilter(
                        $sheet->calculateWorksheetDimension()
                    );

                    $sheet->getStyle('A1:S1')
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle('A1:S1')
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB(
                            'FFE2E8F0'
                        );

                    $sheet->getStyle('A1:S1')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $lastRow =
                        max(
                            1,
                            $sheet->getHighestRow()
                        );

                    $sheet->getStyle(
                        "A1:S{$lastRow}"
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    if ($lastRow >= 2) {
                        $sheet->getStyle(
                            "M2:Q{$lastRow}"
                        )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '₱#,##0.00'
                            );
                    }

                    $sheet->getStyle(
                        "S1:S{$lastRow}"
                    )
                        ->getAlignment()
                        ->setWrapText(true);

                    $sheet->getColumnDimension('B')
                        ->setWidth(26);

                    $sheet->getColumnDimension('C')
                        ->setWidth(18);

                    $sheet->getColumnDimension('D')
                        ->setWidth(20);

                    $sheet->getColumnDimension('L')
                        ->setWidth(20);

                    $sheet->getColumnDimension('R')
                        ->setWidth(22);

                    $sheet->getColumnDimension('S')
                        ->setWidth(34);
                },
        ];
    }
}