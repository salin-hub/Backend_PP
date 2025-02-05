<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function addfavorite(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'books_id' => 'required|exists:books,id',
            'users_id' => 'required|exists:users,id',
        ]);

        // Check if the book is already in the user's favorites
        $existingFavorite = Favorite::where('users_id', $request->users_id)
            ->where('books_id', $request->books_id)
            ->first();

        // If it's already favorited, return true
        if ($existingFavorite) {
            return response()->json(['message' => 'Book is already in favorites.', 'favorite' => true], 200);
        }

        // Otherwise, add the book to favorites
        $favorite = Favorite::create([
            'users_id' => $request->users_id,
            'books_id' => $request->books_id,
        ]);

        return response()->json(['message' => 'Book added to favorites.', 'favorite' => $favorite], 201);
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
        $favorites = Favorite::where('users_id', $userId)
            ->with('book')  // Optionally, eager load the book data
            ->get();

        return response()->json(['favorites' => $favorites]);
    }

    public function list($userId)
    {
        $favorites = Favorite::where('users_id', $userId)->with('book')->get();
        return response()->json(['favorites', $favorites]);
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
