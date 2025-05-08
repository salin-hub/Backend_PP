<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('discount_books', function (Blueprint $table) {
            $table->id('discount_book_id');
            // Make sure the foreign key references 'books(id)'
            $table->foreignId('book_id')->constrained('books', 'id')->onDelete('cascade');
            // Make sure the foreign key references 'discounts(id)'
            $table->foreignId('discount_id')->constrained('discounts', 'discount_id')->onDelete('cascade');
            $table->decimal('final_price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('discount_books');
    }
};
