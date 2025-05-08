<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

   
    protected $fillable = [
        'title',
        'description',
        'author_id',
        'category_id',
        'subcategory_id',  
        'publisher',
        'publish_date',
        'pages',
        'dimensions',
        'language',
        'ean',
        'type',
        'cover_path',
        'price_handbook',
        'quantity',

    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function discountBooks()
    {
        return $this->hasMany(DiscountBook::class);
    }

    public function couponBooks()
    {
        return $this->hasMany(CouponBook::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'book_id');
    }
  
}
