<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('inventory_transactions', function (Blueprint $table) {
        $table->id();

        $table->foreignId('item_id')
            ->constrained()
            ->restrictOnDelete();

        $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

        $table->nullableMorphs('source');

        $table->string('type');

        $table->integer('overall_stock_change')
            ->default(0);

        $table->integer('available_quantity_change')
            ->default(0);

        $table->unsignedInteger('overall_stock_after');

        $table->unsignedInteger('available_quantity_after');

        $table->text('remarks')
            ->nullable();

        $table->timestamp('created_at')
            ->useCurrent();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
