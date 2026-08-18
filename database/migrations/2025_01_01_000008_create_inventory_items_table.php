<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_code', 50)->unique();
            $table->string('item_name', 255);
            $table->enum('item_category', ['MEDICINE', 'SUPPLY', 'EQUIPMENT']);
            $table->integer('reorder_threshold');
            $table->integer('reorder_qty');
            $table->string('item_status', 50)->default('active');
            $table->foreignId('uom_id')->nullable()->constrained('unit_of_measurement', 'uom_id')->restrictOnDelete();
            // Not in the team's ERD table SQL, but the FR note beside the
            // diagram lists "Supplier reference" as part of the item master
            // record — added back so the existing preferred-supplier feature keeps working.
            $table->foreignId('supplier_id')->nullable()->constrained('supplier', 'supplier_id')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
