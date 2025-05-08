<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function rate(Request $request)
    {
        $request->validate([
            'books_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $userId = Auth::id();

        // Check if the user has already rated this product
        $existingRating = Review::where('book_id', $request->product_id)
            ->where('users_id', $userId)
            ->first();

        if ($existingRating) {
            return response()->json(['message' => 'User has already rated this product.'], 400);
        }

        $rating = Review::create([
            'books_id' => $request->product_id,
            'users_id' => $userId,
            'rating' => $request->rating,
        ]);

        return response()->json(['message' => 'Rating submitted successfully.', 'rating' => $rating], 201);
    }
}
