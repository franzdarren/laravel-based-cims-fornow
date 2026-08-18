<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier', function (Blueprint $table) {
            $table->id('supplier_id');
            $table->string('supplier_name', 255);
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_no', 50)->nullable();
            $table->text('address')->nullable();
            // Not in the team's ERD — added so suppliers can be soft-deleted
            // (deletion is blocked/soft in the UI, see Supplier::deletionBlockers()).
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};
