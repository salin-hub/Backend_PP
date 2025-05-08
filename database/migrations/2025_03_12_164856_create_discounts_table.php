<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::create('discounts', function (Blueprint $table) {
        $table->id('discount_id');
        $table->decimal('discount_percentage');
        $table->date('start_date');
        $table->date('end_date');
        $table->text('description');
        $table->timestamps();
    });
}


    public function down(): void {
        Schema::dropIfExists('discounts');
    }
};

