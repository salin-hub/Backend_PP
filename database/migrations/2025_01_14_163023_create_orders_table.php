<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->string('user_id');  // Make sure this matches the user_id type in your users table
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->decimal('total_price', 10, 2);
        $table->timestamps();

        // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
