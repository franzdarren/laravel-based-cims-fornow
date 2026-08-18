<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A 1:1 "detail extension" of a transaction_log row whose
    // transaction_type is ISSUANCE — holds the visit-specific fields that
    // don't belong on the generic ledger row.
    public function up(): void
    {
        Schema::create('issuance_transaction', function (Blueprint $table) {
            $table->id('issuance_transaction_id');
            $table->string('employee_no', 50);
            $table->string('employee_name', 100);
            $table->string('department', 100)->nullable();
            $table->string('employee_supervisor', 100)->nullable();
            $table->text('chief_complaint');
            $table->string('disposition', 255);
            $table->text('remarks')->nullable();
            $table->foreignId('transaction_id')->constrained('transaction_log', 'transaction_id')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuance_transaction');
    }
};
