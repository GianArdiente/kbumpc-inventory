<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table
                ->unsignedInteger('reserved_quantity')
                ->default(0)
                ->after('available_quantity');

            $table->index('reserved_quantity');
        });

        /*
         * Backfill reservations for requests that were already approved
         * before this column was introduced.
         */
        $approvedReservations = DB::table('request_items')
            ->join(
                'inventory_requests',
                'inventory_requests.id',
                '=',
                'request_items.inventory_request_id'
            )
            ->where('inventory_requests.status', 'approved')
            ->select(
                'request_items.item_id',
                DB::raw(
                    'SUM(request_items.requested_quantity) as reserved_total'
                )
            )
            ->groupBy('request_items.item_id')
            ->get();

        foreach ($approvedReservations as $reservation) {
            DB::table('items')
                ->where('id', $reservation->item_id)
                ->update([
                    'reserved_quantity' =>
                        (int) $reservation->reserved_total,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table): void {
            $table->dropIndex(['reserved_quantity']);
            $table->dropColumn('reserved_quantity');
        });
    }
};