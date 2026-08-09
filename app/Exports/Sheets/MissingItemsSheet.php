<?php

namespace App\Exports\Sheets;

use App\Models\InventoryRequest;
use App\Models\InventoryTransaction;
use App\Models\ReturnRecord;
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

class MissingItemsSheet implements
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
        return '06 Missing';
    }

    public function headings(): array
    {
        return [
            'Date',
            'Request Number',
            'Project',
            'Foreman',
            'Work Area',
            'Item Code',
            'Tool / Equipment',
            'Missing Quantity',
            'Unit',
            'Unit Cost',
            'Loss Value',
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
                'source',
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
                'missing'
            )
            ->oldest('created_at')
            ->get()
            ->map(
                function (
                    InventoryTransaction $transaction
                ): array {
                    $returnRecord =
                        $transaction->source instanceof ReturnRecord
                            ? $transaction->source
                            : null;

                    $requestItem =
                        $returnRecord?->requestItem;

                    $inventoryRequest =
                        $requestItem?->inventoryRequest;

                    if (
                        ! $inventoryRequest instanceof InventoryRequest
                    ) {
                        $inventoryRequest = null;
                    }

                    $quantity =
                        abs(
                            (int) $transaction
                                ->overall_stock_change
                        );

                    return [
                        $transaction->created_at
                            ->format('Y-m-d H:i:s'),

                        $inventoryRequest?->request_number
                            ?? '—',

                        $inventoryRequest?->project_name
                            ?? '—',

                        $inventoryRequest?->foreman_name
                            ?? '—',

                        $inventoryRequest?->work_area
                            ?? '—',

                        $transaction->item?->item_code
                            ?? '—',

                        $transaction->item?->name
                            ?? 'Unknown Item',

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

                        $transaction->user?->name
                            ?? 'System',

                        $returnRecord?->remarks
                            ?? $transaction->remarks
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
                            'FFFECACA'
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
                            "J2:K{$lastRow}"
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
                        ->setWidth(20);

                    $sheet->getColumnDimension('E')
                        ->setWidth(20);

                    $sheet->getColumnDimension('G')
                        ->setWidth(24);

                    $sheet->getColumnDimension('M')
                        ->setWidth(32);
                },
        ];
    }
}