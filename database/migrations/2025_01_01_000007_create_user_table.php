<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('fullname', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->boolean('is_active')->default(true);
            $table->foreignId('role_id')->nullable()->constrained('role', 'role_id')->restrictOnDelete();
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
