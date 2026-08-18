<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission_bridge', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('role', 'role_id')->cascadeOnDelete();
            $table->foreignId('role_permission_id')->constrained('role_permission', 'role_permission_id')->cascadeOnDelete();
            $table->primary(['role_id', 'role_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_bridge');
    }
};
