<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Optional for Google users
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        // Optionally drop password_reset_tokens and sessions if you create those tables
        // Schema::dropIfExists('password_reset_tokens');
        // Schema::dropIfExists('sessions');
    }
};
