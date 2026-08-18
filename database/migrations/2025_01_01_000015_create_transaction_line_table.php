<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_line', function (Blueprint $table) {
            $table->id('line_id');
            $table->integer('qty_before')->nullable();
            $table->integer('qty_after')->nullable();
            $table->integer('quantity_issued')->nullable();
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50)->nullable();
            $table->text('line_remarks')->nullable();
            $table->foreignId('transaction_id')->constrained('transaction_log', 'transaction_id')->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batch', 'batch_id')->restrictOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipment', 'equipment_id')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_line');
    }
};
