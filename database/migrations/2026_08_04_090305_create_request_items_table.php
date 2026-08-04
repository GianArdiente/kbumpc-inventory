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
    Schema::create('request_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('inventory_request_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('item_id')
            ->constrained()
            ->restrictOnDelete();

        $table->string('request_type')->default('original');
        $table->unsignedInteger('requested_quantity');
        $table->unsignedInteger('released_quantity')->default(0);
        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};
