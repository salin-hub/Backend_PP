<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'description',
        'image', 
    ];

    // Define the relationship with the Book model
    public function books()
    {
        return $this->hasMany(Book::class);
    }
}