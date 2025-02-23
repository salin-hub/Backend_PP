<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;

class BookController extends Controller
{
    public function __construct()
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }
    public function storeBook(Request $request)
    {
        // Validate the request data including subcategory_id
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id', // Validate subcategory_id
            'publisher' => 'required|string|max:255',
            'publish_date' => 'required|date',
            'pages' => 'required|integer|min:1',
            'dimensions' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'ean' => 'required|string|max:255',
            'type' => 'required|in:handbook',
            'cover_path' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'price_handbook' => 'required|numeric|min:0',
        ]);
    
        // Create a new Book instance
        $book = new Book();
        
        // Fill the validated data (including subcategory_id)
        $book->fill($validated);
    
        // Check if a cover image is uploaded
        if ($request->hasFile('cover_path')) {
            $imageFile = $request->file('cover_path');
    
            // Initialize the Cloudinary upload API
            $uploadApi = new UploadApi();
            $uploadedFile = $uploadApi->upload(
                $imageFile->getRealPath(),
                [
                    'folder' => 'imageBooks',
                ]
            );
    
            // Get the secure URL of the uploaded image
            $imageUrl = $uploadedFile['secure_url'];
    
            // Save the image URL to the cover_path field
            $book->cover_path = $imageUrl;
        }
    
        // Save the new book to the database
        $book->save();
    
        // Return a response with the created book and a success message
        return response()->json(['message' => 'Book created successfully!', 'book' => $book], 201);
    }
    
    /**
     * Retrieve all books.
     */
    public function getAllBooks()
    {
        $books = Book::with(['author', 'category'])->get();
        return response()->json([
            'message' => 'Books retrieved successfully',
            'books' => $books,
        ], 200);
    }

    public function getBookById($id)
    {
        // Retrieve the book with its author and category
        $book = Book::with(['author', 'category'])->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Retrieve related books (same category or same author, excluding the current book)
        $relatedBooks = Book::where('id', '!=', $id) // Exclude the current book
            ->where(function ($query) use ($book) {
                $query->where('category_id', $book->category_id) // Same category
                    ->orWhere('author_id', $book->author_id); // Or same author
            })
            ->take(5) // Limit to 5 related books
            ->get();

        return response()->json([
            'message' => 'Book retrieved successfully',
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ], 200);
    }
    public function update(Request $request, $id)
{
    // Validate the incoming request
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'author_id' => 'required|integer|exists:authors,id',
        'category_id' => 'required|integer|exists:categories,id',
        'publisher' => 'nullable|string|max:255',
        'publish_date' => 'nullable|date',
        'pages' => 'nullable|integer',
        'dimensions' => 'nullable|string',
        'language' => 'nullable|string|max:255',
        'ean' => 'nullable|string|max:255',
        'price_handbook' => 'nullable|numeric|required_if:type,handbook',
    ]);

    // Find the book by ID
    $book = Book::findOrFail($id);
    $book->fill($validated); // Fill the validated fields

    // Check if a new cover image is uploaded
    if ($request->hasFile('cover_path') && $request->file('cover_path')->isValid()) {
        try {
            // Delete the old cover image from Cloudinary if it exists
            if ($book->cover_path) {
                $uploadApi = new UploadApi();
                $publicId = basename(parse_url($book->cover_path, PHP_URL_PATH), '.' . pathinfo($book->cover_path, PATHINFO_EXTENSION));
                $uploadApi->destroy($publicId); // Delete the old image from Cloudinary
            }

            // Upload the new cover image to Cloudinary
            $imageFile = $request->file('cover_path');
            $uploadApi = new UploadApi();
            $uploadedFile = $uploadApi->upload($imageFile->getRealPath(), [
                'folder' => 'imageBooks', // Set the folder where images should be uploaded
            ]);

            // Save the new image URL in the book record
            $book->cover_path = $uploadedFile['secure_url'];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Image upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Save the updated book data
    $book->save();

    return response()->json([
        'message' => 'Book updated successfully',
        'book' => $book, // Return the updated book data
    ]);
}








    /**
     * Delete a book.
     */
    public function deleteBook($id)
    {
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Delete associated files
        if ($book->cover_path) {
            Storage::disk('public')->delete($book->cover_path);
        }

        if ($book->type === 'ebook' && $book->file_path) {
            Storage::disk('public')->delete($book->file_path);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
    public function searchBooks(Request $request)
{
    // Create a query builder instance for the Book model
    $query = Book::query();

    // Filter by title if provided
    if ($request->filled('title')) {
        $searchTerm = $request->title;

        // Search for books by title or author name (assuming 'author' is a relationship on the Book model)
        $query->where(function ($q) use ($searchTerm) {
            $q->where('title', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('author', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                });
        });
    }

    // Filter by category if provided
    if ($request->filled('category') && $request->category !== 'All') {
        $category = $request->category;
        $query->whereHas('category', function ($q) use ($category) {
            $q->where('name', 'like', '%' . $category . '%');
        });
    }

    // Execute the query and eager load the 'author' and 'category' relationships
    $books = $query->with(['author', 'category'])->get();

    // Check if no books are found
    if ($books->isEmpty()) {
        // Return a 200 response with an empty list and a message
        return response()->json([
            'message' => 'Your result is not found.',
            'books' => [], // No books to return
        ], 200);
    }

    // Return the response with books data
    return response()->json([
        'message' => 'Books retrieved successfully',
        'books' => $books,
    ], 200);
}

    public function getNewBooks()
    {
        // Define the timeframe (e.g., 30 days ago)
        $dateThreshold = Carbon::now()->subDays(30);

        // Retrieve books created after the threshold date
        $books = Book::where('created_at', '>=', $dateThreshold)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'New books retrieved successfully.',
            'data' => $books,
        ]);
    }
    public function getBookRecommendations(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $preferredGenre = $user->preferred_genre;

        $books = Book::where('genre', $preferredGenre)
            ->inRandomOrder()
            ->take(5)
            ->get();

        return response()->json([
            'message' => 'Book recommendations based on your preferences.',
            'data' => $books,
        ]);
    }
}