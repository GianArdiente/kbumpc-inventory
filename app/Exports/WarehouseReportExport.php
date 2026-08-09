<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WarehouseReportExport implements WithMultipleSheets
{
    public function __construct(
        private readonly string $dateFrom,
        private readonly string $dateTo
    ) {
    }

    /**
     * Return every worksheet that should appear
     * inside the warehouse report workbook.
     *
     * Individual sheet classes will be created
     * one at a time after this coordinator.
     */
    public function sheets(): array
    {
        return [
            new Sheets\SummarySheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\MaterialsUsedSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\ToolsMobilizedSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\GoodReturnsSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\DamagedItemsSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\MissingItemsSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\StockInSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\AdjustmentsSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\CompletedRequestsSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\InventorySnapshotSheet(
                $this->dateFrom,
                $this->dateTo
            ),

            new Sheets\TransactionLedgerSheet(
                $this->dateFrom,
                $this->dateTo
            ),
        ];
    }
}