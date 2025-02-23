<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('author_id')->constrained()->onDelete('cascade'); // Foreign key to authors table
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // Foreign key to categories table
            $table->unsignedBigInteger('subcategory_id')->nullable(); // Define subcategory_id as unsignedBigInteger

            // Define the foreign key constraint for subcategory_id (ensure that subcategory_id is nullable)
            $table->foreign('subcategory_id')->references('id')->on('sub_categories')->onDelete('set null');

            $table->string('publisher');
            $table->date('publish_date');
            $table->integer('pages');
            $table->string('dimensions');
            $table->string('language');
            $table->string('ean');
            $table->enum('type', ['handbook']); // Only 'handbook' type
            $table->string('cover_path'); // Cloudinary URL for the cover image
            $table->decimal('price_handbook', 8, 2); // Price for the handbook
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('books');
    }
}
