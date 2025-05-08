<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = ['coupon_code', 'discount_percentage', 'start_date', 'end_date', 'usage_limit', 'minimum_order_value', 'description'];

    public function couponBooks()
    {
        return $this->hasMany(CouponBook::class);
    }

    public function couponUsages()
    {
        return $this->hasMany(CouponUsage::class);
    }
}

