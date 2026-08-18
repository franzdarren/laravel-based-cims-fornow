<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The central, immutable ledger: every posted stock-affecting action
    // (a receiving approval, an issuance, a disposal, or a manual
    // adjustment) is one row here, with its line-level detail in
    // transaction_line.
    public function up(): void
    {
        Schema::create('transaction_log', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->timestamp('transaction_datetime')->useCurrent();
            $table->enum('transaction_type', ['ADJUSTMENT', 'ISSUANCE', 'DISPOSAL', 'RECEIVING']);
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('user', 'user_id')->restrictOnDelete();
            $table->foreignId('receiving_transaction_id')->nullable()->constrained('receiving_transaction', 'receiving_transaction_id')->restrictOnDelete();
            // Not in the team's ERD table SQL — added so ISSUANCE/DISPOSAL/
            // ADJUSTMENT ledger entries have a human-readable reference number
            // too (RECEIVING entries already have one via receiving_transaction.ref_no).
            $table->string('reference_no', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_log');
    }
};
