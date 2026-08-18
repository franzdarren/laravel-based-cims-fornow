<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Not in the team's ERD (which has no persisted-report concept at all) —
    // kept so generated reports remain reopenable/re-exportable, as decided.
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Stock Balance', 'Transaction History', 'Equipment Registry / Status']);
            $table->foreignId('generated_by')->constrained('user', 'user_id')->restrictOnDelete();
            $table->string('period');
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
