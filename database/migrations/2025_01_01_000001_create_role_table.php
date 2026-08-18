<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id('role_id');
            $table->string('role_name', 50)->unique();
            $table->string('role_description', 255)->nullable();
            // Not in the team's ERD — added so a role can be deactivated
            // (blocked while active users are assigned to it), as already built.
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
