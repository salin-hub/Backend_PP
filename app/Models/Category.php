<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    // Define relationship with SubCategory
    public function subcategories()
    {
        return $this->hasMany(SubCategory::class);
    }
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
