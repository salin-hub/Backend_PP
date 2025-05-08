<?php

namespace App\Models;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponBook extends Model
{
    use HasFactory;

    protected $fillable = ['book_id', 'coupon_id'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
