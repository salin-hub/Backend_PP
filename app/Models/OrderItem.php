<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    // Specify the fillable fields for mass assignment
    protected $fillable = ['order_id', 'books_id', 'quantity', 'price','status', 'message'];

    /**
     * Relationship with the Order model.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with the Book model.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'books_id');
    }
    
   
}
