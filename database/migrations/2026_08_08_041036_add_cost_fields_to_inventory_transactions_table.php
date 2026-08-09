<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'inventory_transactions',
            function (Blueprint $table): void {
                $table->decimal(
                    'unit_cost_snapshot',
                    12,
                    2
                )
                    ->nullable()
                    ->after('type');

                $table->decimal(
                    'total_cost',
                    14,
                    2
                )
                    ->nullable()
                    ->after('unit_cost_snapshot');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'inventory_transactions',
            function (Blueprint $table): void {
                $table->dropColumn([
                    'unit_cost_snapshot',
                    'total_cost',
                ]);
            }
        );
    }
};