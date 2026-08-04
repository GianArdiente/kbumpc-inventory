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
    Schema::create('return_records', function (Blueprint $table) {

        $table->id();

        $table->foreignId('request_item_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('recorded_by')
            ->constrained('users')
            ->restrictOnDelete();

        $table->unsignedInteger('good_quantity')->default(0);
        $table->unsignedInteger('damaged_quantity')->default(0);
        $table->unsignedInteger('missing_quantity')->default(0);

        $table->dateTime('returned_at');

        $table->text('remarks')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_records');
    }
};
