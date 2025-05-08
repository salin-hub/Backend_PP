<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->date('used_date')->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropColumn('used_date');
        });
    }
};
