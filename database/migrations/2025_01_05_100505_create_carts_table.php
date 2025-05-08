<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
{
    Schema::create('carts', function (Blueprint $table) {
        $table->id();
        $table->string('user_id');  // Make sure this matches the user_id type in your users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->unsignedBigInteger('book_id'); // Add this line
        $table->integer('quantity');
        $table->timestamps();

        // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
    });
}

public function down()
{
    Schema::dropIfExists('carts');
}

};
