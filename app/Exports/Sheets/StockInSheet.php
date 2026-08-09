<?php

namespace App\Exports\Sheets;

use App\Models\InventoryTransaction;
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

class StockInSheet implements
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
        return '07 Stock In';
    }

    public function headings(): array
    {
        return [
            'Date',
            'Item Code',
            'Item',
            'Category',
            'Consumption Type',
            'Quantity Added',
            'Unit',
            'Unit Cost',
            'Stock Received Value',
            'Overall Stock After',
            'Physical Stock After',
            'Recorded By',
            'Remarks',
        ];
    }

    public function collection(): Collection
    {
        $periodStart =
            $this->dateFrom . ' 00:00:00';

        $periodEnd =
            $this->dateTo . ' 23:59:59';

        return InventoryTransaction::query()
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
            ->where(
                'type',
                'stock_in'
            )
            ->oldest('created_at')
            ->get()
            ->map(
                function (
                    InventoryTransaction $transaction
                ): array {
                    $quantity =
                        max(
                            abs(
                                (int) $transaction
                                    ->overall_stock_change
                            ),
                            abs(
                                (int) $transaction
                                    ->available_quantity_change
                            )
                        );

                    return [
                        $transaction->created_at
                            ->format('Y-m-d H:i:s'),

                        $transaction->item?->item_code
                            ?? '—',

                        $transaction->item?->name
                            ?? 'Unknown Item',

                        $transaction->item?->category
                            ?? '—',

                        $transaction->item?->consumption_type
                            ?? '—',

                        $quantity,

                        $transaction->item?->unit
                            ?? '—',

                        $transaction->unit_cost_snapshot !== null
                            ? (float) $transaction
                                ->unit_cost_snapshot
                            : null,

                        $transaction->total_cost !== null
                            ? abs(
                                (float) $transaction
                                    ->total_cost
                            )
                            : null,

                        $transaction->overall_stock_after,

                        $transaction->available_quantity_after,

                        $transaction->user?->name
                            ?? 'System',

                        $transaction->remarks
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

                    $sheet->getStyle('A1:M1')
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle('A1:M1')
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB(
                            'FFD1FAE5'
                        );

                    $sheet->getStyle('A1:M1')
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
                        "A1:M{$lastRow}"
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    if ($lastRow >= 2) {
                        $sheet->getStyle(
                            "H2:I{$lastRow}"
                        )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '₱#,##0.00'
                            );
                    }

                    $sheet->getStyle(
                        "M1:M{$lastRow}"
                    )
                        ->getAlignment()
                        ->setWrapText(true);

                    $sheet->getColumnDimension('C')
                        ->setWidth(24);

                    $sheet->getColumnDimension('D')
                        ->setWidth(18);

                    $sheet->getColumnDimension('E')
                        ->setWidth(20);

                    $sheet->getColumnDimension('M')
                        ->setWidth(32);
                },
        ];
    }
}