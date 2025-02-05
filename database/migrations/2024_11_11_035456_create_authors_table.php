<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorsTable extends Migration
{
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id(); // Creates BIGINT UNSIGNED primary key
            $table->string('name'); // Author's name
            $table->string('email')->unique(); // Unique email field
            $table->text('description')->nullable(); // Optional description
            $table->string('image')->nullable(); // Optional image field
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('authors');
    }
}