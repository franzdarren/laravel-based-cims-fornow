<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch', function (Blueprint $table) {
            $table->id('batch_id');
            $table->string('batch_no', 100)->nullable();
            $table->integer('quantity_on_hand');
            $table->date('expiry_date')->nullable();
            $table->integer('quantity_received');
            $table->string('brand', 100)->nullable();
            $table->enum('batch_status', ['ACTIVE', 'INACTIVE', 'FOR_DISPOSAL', 'DISPOSED']);
            $table->foreignId('item_id')->nullable()->constrained('inventory_items', 'item_id')->restrictOnDelete();
            $table->foreignId('receive_transaction_id')->nullable()->constrained('receiving_transaction', 'receiving_transaction_id')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch');
    }
};
