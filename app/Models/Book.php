<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'title',
        'description',
        'author_id',
        'category_id',
        'subcategory_id',  // Add subcategory_id here
        'publisher',
        'publish_date',
        'pages',
        'dimensions',
        'language',
        'ean',
        'type',
        'cover_path',
        'price_handbook',
    ];

    // Define the relationship with Author model
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    // Define the relationship with Category model
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'books_id');
    }
}