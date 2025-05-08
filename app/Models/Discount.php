<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $primaryKey = 'discount_id'; // Important because your primary key is not the default 'id'

    protected $fillable = [
        'discount_percentage',
        'start_date',
        'end_date',
        'description',
    ];

    /**
     * Get all discount book entries related to this discount.
     */
    public function discountBooks()
    {
        return $this->hasMany(DiscountBook::class, 'discount_id', 'discount_id');
    }
}
