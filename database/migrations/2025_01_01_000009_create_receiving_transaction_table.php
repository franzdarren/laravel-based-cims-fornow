<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receiving_transaction', function (Blueprint $table) {
            $table->id('receiving_transaction_id');
            // CANCELLED isn't in the team's ERD enum (PENDING/APPROVED/RETURNED) —
            // added back so the nurse's "cancel pending request" action keeps working.
            $table->enum('status', ['PENDING', 'APPROVED', 'RETURNED', 'CANCELLED']);
            $table->string('ref_no', 100)->unique();
            $table->timestamp('date_received')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('supplier', 'supplier_id')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('user', 'user_id')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('user', 'user_id')->restrictOnDelete();
            // Not in the team's ERD table SQL — added back so delivery remarks,
            // supervisor return reasons, nurse cancellation reasons, and the
            // approve/return decision timestamp keep working as already built.
            $table->string('remarks', 150)->nullable();
            $table->string('return_reason', 150)->nullable();
            $table->string('cancel_reason', 255)->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_transaction');
    }
};
