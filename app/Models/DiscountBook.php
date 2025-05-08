<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class DiscountBook extends Model
{
    use HasFactory;
    protected $primaryKey = 'discount_book_id';

    protected $fillable = [
        'book_id',
        'discount_id',
        'final_price',
    ];
    // protected $fillable = ['book_id', 'discount_book_id', 'final_price'];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    /**
     * Get the discount associated with the discount book.
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    // Automatically calculate final_price before saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($discountBook) {
            $book = Book::find($discountBook->book_id);
            $discount = Discount::find($discountBook->discount_id);

            if ($book && $discount) {
                $originalPrice = $book->price_handbook; // Ensure price_handbook exists
                $discountPercentage = $discount->discount_percentage; // Ensure column exists
                $finalPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

                $discountBook->final_price = round($finalPrice, 2); // Round to 2 decimal places
            }
        });
    }
}



