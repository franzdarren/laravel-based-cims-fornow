<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Not part of the team's ERD. transaction_line can only point at a
    // batch/equipment row, but those rows don't exist until a receiving
    // request is approved — so there's nowhere in the ERD to persist the
    // requested lines of a PENDING/RETURNED receiving_transaction. This
    // table fills that gap; approve() turns each row here into a real
    // batch/equipment row plus a posted transaction_log/transaction_line entry.
    public function up(): void
    {
        Schema::create('receiving_transaction_line', function (Blueprint $table) {
            $table->id('receiving_transaction_line_id');
            $table->foreignId('receiving_transaction_id')->constrained('receiving_transaction', 'receiving_transaction_id')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items', 'item_id')->restrictOnDelete();
            $table->integer('quantity');
            $table->string('brand', 100)->nullable();
            $table->string('batch_no', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('asset_tag', 100)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('location', 'location_id')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_transaction_line');
    }
};
