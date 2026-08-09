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

class TransactionLedgerSheet implements
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
        return '11 Transaction Ledger';
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Date',
            'Type',
            'Item Code',
            'Item',
            'Category',
            'Consumption Type',
            'Unit',
            'Overall Change',
            'Physical Change',
            'Overall Stock After',
            'Physical Stock After',
            'Unit Cost Snapshot',
            'Transaction Value',
            'Recorded By',
            'Source Type',
            'Source ID',
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
            ->oldest('created_at')
            ->oldest('id')
            ->get()
            ->map(
                function (
                    InventoryTransaction $transaction
                ): array {
                    return [
                        $transaction->id,

                        $transaction->created_at
                            ->format('Y-m-d H:i:s'),

                        $this->formatType(
                            $transaction->type
                        ),

                        $transaction->item?->item_code
                            ?? '—',

                        $transaction->item?->name
                            ?? 'Unknown Item',

                        $transaction->item?->category
                            ?? '—',

                        $transaction->item?->consumption_type
                            ?? '—',

                        $transaction->item?->unit
                            ?? '—',

                        $transaction->overall_stock_change,

                        $transaction->available_quantity_change,

                        $transaction->overall_stock_after,

                        $transaction->available_quantity_after,

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

                        $transaction->user?->name
                            ?? 'System',

                        $this->formatSourceType(
                            $transaction->source_type
                        ),

                        $transaction->source_id
                            ?? '—',

                        $transaction->remarks
                            ?? '',
                    ];
                }
            );
    }

    private function formatType(
        ?string $type
    ): string {
        if ($type === null || $type === '') {
            return 'Unknown';
        }

        return ucwords(
            str_replace(
                '_',
                ' ',
                $type
            )
        );
    }

    private function formatSourceType(
        ?string $sourceType
    ): string {
        if (
            $sourceType === null ||
            $sourceType === ''
        ) {
            return '—';
        }

        $parts =
            explode(
                '\\',
                $sourceType
            );

        return end($parts)
            ?: $sourceType;
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

                    /*
                     * Keep headings visible while scrolling.
                     */
                    $sheet->freezePane('A2');

                    /*
                     * Filters allow staff to quickly inspect
                     * a specific item, transaction type, user,
                     * or source.
                     */
                    $sheet->setAutoFilter(
                        $sheet->calculateWorksheetDimension()
                    );

                    /*
                     * Heading style.
                     */
                    $sheet->getStyle('A1:R1')
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle('A1:R1')
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB(
                            'FFE2E8F0'
                        );

                    $sheet->getStyle('A1:R1')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_CENTER
                        );

                    $lastRow =
                        max(
                            1,
                            $sheet->getHighestRow()
                        );

                    /*
                     * Table borders.
                     */
                    $sheet->getStyle(
                        "A1:R{$lastRow}"
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    /*
                     * Historical cost columns.
                     */
                    if ($lastRow >= 2) {
                        $sheet->getStyle(
                            "M2:N{$lastRow}"
                        )
                            ->getNumberFormat()
                            ->setFormatCode(
                                '₱#,##0.00'
                            );
                    }

                    /*
                     * Center numeric stock movement columns.
                     */
                    if ($lastRow >= 2) {
                        $sheet->getStyle(
                            "I2:L{$lastRow}"
                        )
                            ->getAlignment()
                            ->setHorizontal(
                                Alignment::HORIZONTAL_CENTER
                            );
                    }

                    /*
                     * Remarks can be long.
                     */
                    $sheet->getStyle(
                        "R1:R{$lastRow}"
                    )
                        ->getAlignment()
                        ->setWrapText(true);

                    /*
                     * Give descriptive columns enough room.
                     */
                    $sheet->getColumnDimension('B')
                        ->setWidth(20);

                    $sheet->getColumnDimension('C')
                        ->setWidth(20);

                    $sheet->getColumnDimension('E')
                        ->setWidth(26);

                    $sheet->getColumnDimension('F')
                        ->setWidth(18);

                    $sheet->getColumnDimension('G')
                        ->setWidth(20);

                    $sheet->getColumnDimension('O')
                        ->setWidth(22);

                    $sheet->getColumnDimension('P')
                        ->setWidth(24);

                    $sheet->getColumnDimension('R')
                        ->setWidth(38);
                },
        ];
    }
}