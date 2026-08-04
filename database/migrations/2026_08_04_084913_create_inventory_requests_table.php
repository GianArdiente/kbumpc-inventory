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
    Schema::create('inventory_requests', function (Blueprint $table) {
        $table->id();
        $table->string('request_number')->unique();
        $table->string('foreman_name');
        $table->string('project_name');
        $table->string('work_area');
        $table->string('status')->default('pending');
        $table->dateTime('requested_at');

        $table->foreignId('encoded_by')
            ->constrained('users')
            ->restrictOnDelete();

        $table->foreignId('approved_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->dateTime('approved_at')->nullable();

        $table->foreignId('mobilized_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->dateTime('mobilized_at')->nullable();
        $table->dateTime('completed_at')->nullable();
        $table->text('remarks')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_requests');
    }
};
