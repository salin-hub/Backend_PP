<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRememberTokenToUsersTable extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->rememberToken()->nullable();  // Add 'remember_token' column
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'remember_token')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
}
