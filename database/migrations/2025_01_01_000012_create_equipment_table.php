<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id('equipment_id');
            $table->string('asset_tag', 100)->unique();
            $table->string('serial_number', 100)->unique();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            // Corrected the ERD's 'DISPODED' spelling to 'DISPOSED'.
            $table->enum('equipment_status', ['AVAILABLE', 'ISSUED', 'UNDER_MAINTENANCE', 'MISSING', 'DISPOSED']);
            $table->foreignId('item_id')->nullable()->constrained('inventory_items', 'item_id')->restrictOnDelete();
            $table->foreignId('receive_transaction_id')->nullable()->constrained('receiving_transaction', 'receiving_transaction_id')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('location', 'location_id')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
