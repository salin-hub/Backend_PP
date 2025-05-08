<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
{
    Schema::create('coupon_usages', function (Blueprint $table) {
        $table->id('usage_id');
        $table->unsignedBigInteger('coupon_id');
        $table->string('user_id');  // Make sure this matches the user_id type in your users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->timestamps();

        $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
        // $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
    });
}
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
    }
};
