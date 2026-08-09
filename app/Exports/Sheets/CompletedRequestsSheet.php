<?php

namespace App\Exports\Sheets;

use App\Models\InventoryRequest;
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

class CompletedRequestsSheet implements
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
        return '09 Completed Requests';
    }

    public function headings(): array
    {
        return [
            'Request Number',
            'Foreman',
            'Project',
            'Work Area',
            'Status',
            'Item Lines',
            'Requested Units',
            'Released Units',
            'Approved At',
            'Mobilized At',
            'Completed At',
            'Encoded By',
        ];
    }

    public function collection(): Collection
    {
        $periodStart =
            $this->dateFrom . ' 00:00:00';

        $periodEnd =
            $this->dateTo . ' 23:59:59';

        return InventoryRequest::query()
            ->with([
                'encoder',
                'items',
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
            ->oldest('completed_at')
            ->get()
            ->map(
                function (
                    InventoryRequest $inventoryRequest
                ): array {
                    $requestedUnits =
                        (int) $inventoryRequest
                            ->items
                            ->sum(
                                'requested_quantity'
                            );

                    $releasedUnits =
                        (int) $inventoryRequest
                            ->items
                            ->sum(
                                'released_quantity'
                            );

                    return [
                        $inventoryRequest
                            ->request_number,

                        $inventoryRequest
                            ->foreman_name,

                        $inventoryRequest
                            ->project_name,

                        $inventoryRequest
                            ->work_area,

                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $inventoryRequest
                                    ->status
                            )
                        ),

                        $inventoryRequest
                            ->items
                            ->count(),

                        $requestedUnits,

                        $releasedUnits,

                        $inventoryRequest
                            ->approved_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                            ?? '—',

                        $inventoryRequest
                            ->mobilized_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                            ?? '—',

                        $inventoryRequest
                            ->completed_at
                            ?->format(
                                'Y-m-d H:i:s'
                            )
                            ?? '—',

                        $inventoryRequest
                            ->encoder?->name
                            ?? 'System',
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

                    $sheet->getStyle('A1:L1')
                        ->getFont()
                        ->setBold(true);

                    $sheet->getStyle('A1:L1')
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB(
                            'FFDBEAFE'
                        );

                    $sheet->getStyle('A1:L1')
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
                        "A1:L{$lastRow}"
                    )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        );

                    $sheet->getColumnDimension('B')
                        ->setWidth(20);

                    $sheet->getColumnDimension('C')
                        ->setWidth(28);

                    $sheet->getColumnDimension('D')
                        ->setWidth(22);

                    $sheet->getColumnDimension('L')
                        ->setWidth(22);
                },
        ];
    }
}