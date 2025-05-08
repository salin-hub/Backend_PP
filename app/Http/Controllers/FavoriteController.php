<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function addFavorite(Request $request)
{
    // Validate incoming request
    $validated = $request->validate([
        'books_id' => 'required|exists:books,id',
        'user_id' => 'required|exists:users,id',
    ]);

    // Check if the book is already in the user's favorites
    $existingFavorite = Favorite::where('user_id', $validated['user_id'])
        ->where('books_id', $validated['books_id'])
        ->first();

    if ($existingFavorite) {
        return response()->json([
            'message' => 'Book is already in favorites.',
            'favorite' => true
        ], 200);
    }

    // Add the book to favorites
    $favorite = Favorite::create([
        'user_id' => $validated['user_id'],
        'books_id' => $validated['books_id'],
    ]);

    return response()->json([
        'message' => 'Book added to favorites.',
        'favorite' => $favorite
    ], 201);
}


    public function deleteFavorite(Request $request)
    {
        $request->validate([
            'books_id' => 'required|exists:books,id',
            'users_id' => 'required|exists:users,id',
        ]);

        // Find the favorite to delete
        $favorite = Favorite::where('users_id', $request->users_id)
            ->where('books_id', $request->books_id)
            ->first();

        // Check if the favorite exists
        if (!$favorite) {
            return response()->json(['message' => 'Favorite not found.'], 404);
        }

        // Delete the favorite
        $favorite->delete();

        return response()->json(['message' => 'Favorite removed successfully.'], 200);
    }


    public function getUserFavorites($userId)
    {
        $favorites = Favorite::where('user_id', $userId)
            ->with('book') // Eager load the book relationship
            ->get()
            ->map(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'book' => $favorite->book,
                    'created_at' => $favorite->created_at->toDateTimeString(),
                ];
            });
    
        return response()->json(['favorites' => $favorites]);
    }
    

    // public function list($userId)
    // {
    //     $favorites = Favorite::where('users_id', $userId)->with('book','book.author','book.category', 'book.subCategory', 'book.discountBooks.discount', 'book.reviews')->get();
    //     return response()->json(['favorites', $favorites]);
    // }
    public function list($userId)
{
    $favorites = Favorite::where('user_id', $userId)
        ->with('book.author', 'book.category', 'book.subCategory', 'book.discountBooks.discount', 'book.reviews')
        ->get()
        ->map(function ($favorite) {
            $book = $favorite->book;

            $totalReviews = $book->reviews->count();
            $averageRating = $totalReviews > 0 ? number_format($book->reviews->avg('rating'), 2) : '0.00';

            $discount = $book->discountBooks()
                ->whereHas('discount', function ($query) {
                    $query->where('start_date', '<=', now())
                          ->where('end_date', '>=', now());
                })
                ->latest()
                ->first();

            $discountedPrice = $discount
                ? round($book->price_handbook - ($book->price_handbook * (optional($discount->discount)->discount_percentage / 100)), 2)
                : null;

            return [
                'id' => $book->id,
                'title' => $book->title,
                'description' => $book->description,
                'author_id' => $book->author_id,
                'category_id' => $book->category_id,
                'sub_category_id' => $book->subcategory_id,
                'publisher' => $book->publisher,
                'publish_date' => $book->publish_date,
                'pages' => $book->pages,
                'dimensions' => $book->dimensions,
                'language' => $book->language,
                'ean' => $book->ean,
                'type' => $book->type,
                'cover_path' => $book->cover_path,
                'original_price' => $book->price_handbook,
                'has_discount' => $discount ? true : false,
                'discounted_price' => $discountedPrice,
                'ratingCount' => $averageRating,
                'reviewcount' => $totalReviews,
                'sales_count' => $book->sales_count,
                'author' => [
                    'id' => $book->author->id,
                    'name' => $book->author->name,
                    'email' => $book->author->email,
                    'description' => $book->author->description,
                    'image' => $book->author->image,
                ],
                'category' => [
                    'id' => $book->category->id,
                    'name' => $book->category->name,
                    'description' => $book->category->description,
                ],
                'sub_category' => [
                    'id' => $book->subCategory->id,
                    'name' => $book->subCategory->name,
                ],
                'discount' => $discount ? [
                    'id' => $discount->discount_id,
                    'discount_percentage' => $discount->discount->discount_percentage,
                    'start_date' => $discount->discount->start_date,
                    'end_date' => $discount->discount->end_date,
                    'description' => $discount->discount->description,
                ] : null
            ];
        });

    return response()->json(['favorites' => $favorites]);
}

    public function getMostFavoritedBooks()
    {
        try {
            $books = Book::withCount('favorites') // Add a count of related favorites
                ->having('favorites_count', '>=', 2) // Filter by the computed favorites count
                ->orderBy('favorites_count', 'desc') // Order by the count in descending order
                ->take(10) // Limit to top 10 books
                ->get();

            return response()->json([
                'message' => 'Most favorited books retrieved successfully.',
                'data' => $books,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving most favorited books.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
