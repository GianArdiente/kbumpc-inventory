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
    Schema::create('items', function (Blueprint $table) {
        $table->id();
        $table->string('item_code')->unique();
        $table->string('name');
        $table->string('category');
        $table->string('consumption_type');
        $table->string('unit');
        $table->unsignedInteger('overall_stock')->default(0);
        $table->unsignedInteger('available_quantity')->default(0);
        $table->unsignedInteger('minimum_stock_level')->default(0);
        $table->string('storage_location')->nullable();
        $table->text('remarks')->nullable();
        $table->timestamp('archived_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
