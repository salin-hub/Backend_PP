<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Book;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

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
        try {
            // Validate the request data including subcategory_id
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'author_id' => 'required|exists:authors,id',
                'category_id' => 'required|exists:categories,id',
                'subcategory_id' => 'nullable|exists:sub_categories,id',
                'publisher' => 'required|string|max:255',
                'publish_date' => 'required|date',
                'pages' => 'required|integer|min:1',
                'dimensions' => 'required|string|max:255',
                'language' => 'required|string|max:255',
                'ean' => 'required|string|max:255',
                'type' => 'required|in:handbook',
                'cover_path' => 'required|file|mimes:jpg,jpeg,png|max:2048',
                'price_handbook' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
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
        } catch (\Exception $e) {
            // Log the error and return a response with the error message
         
            return response()->json(['error' => 'Failed to create book. Please try again later.'], 500);
        }
    }


    /**
     * Retrieve all books.
     */

    public function getBookRecommendations(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        if (!$user->preferred_category_id) {
            return response()->json(['error' => 'No preferred category found for user.'], 400);
        }

        // Fetch books based on the user's preferred category
        $books = Book::where('category_id', $user->preferred_category_id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        if ($books->isEmpty()) {
            return response()->json([
                'message' => 'No books found for your preferred category.',
                'data' => [],
            ], 404);
        }

        return response()->json([
            'message' => 'Recommended books for you.',
            'data' => $books,
        ]);
    }

    public function getAllBooks()
    {
        $books = Book::with(['author', 'category', 'subCategory', 'discountBooks.discount', 'reviews'])
            ->get()
            ->map(function ($book) {
                $totalReviews = $book->reviews->count();
                $averageRating = $totalReviews > 0 ? number_format($book->reviews->avg('rating'), 2) : '0.00';

                $discount = $book->discountBooks()
                    ->whereHas('discount', function ($query) {
                        $query->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    })
                    ->latest()
                    ->first();

                $discountedPrice = $discount && $discount->discount
                    ? round($book->price_handbook - ($book->price_handbook * ($discount->discount->discount_percentage / 100)), 2)
                    : null;

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'description' => $book->description,
                    'author_id' => $book->author_id,
                    'category_id' => $book->category_id,
                    'sub_category_id' => $book->sub_category_id,
                    'publisher' => $book->publisher,
                    'publish_date' => $book->publish_date,
                    'pages' => $book->pages,
                    'dimensions' => $book->dimensions,
                    'language' => $book->language,
                    'ean' => $book->ean,
                    'type' => $book->type,
                    'cover_path' => $book->cover_path,
                    'original_price' => $book->price_handbook,
                    'has_discount' => $discount && $discount->discount ? true : false,
                    'discounted_price' => $discountedPrice,
                    'ratingCount' => $averageRating,
                    'reviewcount' => $totalReviews,
                    'sales_count' => $book->sales_count,
                    'quantity'=>$book->quantity,
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
                    'discount' => $discount && $discount->discount ? [
                        'id' => $discount->discount->id,
                        'discount_percentage' => $discount->discount->discount_percentage,
                        'start_date' => $discount->discount->start_date,
                        'end_date' => $discount->discount->end_date,
                        'description' => $discount->discount->description,
                    ] : null
                ];
            });

        return response()->json(['books' => $books]);
    }


    public function updateBook(Request $request, $bookId)
    {
        // Validate the request data
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'required|exists:categories,id',
            'publisher' => 'required|string|max:255',
            'publish_date' => 'required|date',
            'pages' => 'required|integer|min:1',
            'dimensions' => 'required|string|max:255',
            'language' => 'required|string|max:255',
            'ean' => 'required|string|max:255',
            'type' => 'required|in:handbook',
            'cover_path' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Handling optional cover image upload
            'price_handbook' => 'required|numeric|min:0',
        ]);

        // Find the book by ID
        $book = Book::findOrFail($bookId);

        // Fill the validated data (excluding cover image initially)
        $book->fill($validated);

        // Check if a new cover image is uploaded
        if ($request->hasFile('cover_path')) {
            // Delete the old image from Cloudinary if it exists
            if ($book->cover_path) {
                $publicId = last(explode('/', parse_url($book->cover_path, PHP_URL_PATH)));
                $uploadApi = new UploadApi();
                $uploadApi->destroy($publicId);
            }

            // Upload the new image to Cloudinary
            $imageFile = $request->file('cover_path');
            $uploadedFile = $uploadApi->upload(
                $imageFile->getRealPath(),
                ['folder' => 'imageBooks']
            );

            // Get the secure URL of the uploaded image
            $book->cover_path = $uploadedFile['secure_url'];
        }

        // Save the updated book to the database
        $book->save();

        // Return a response with the updated book
        return response()->json(['message' => 'Book updated successfully!', 'book' => $book], 200);
    }



    public function getBookById($id)
    {
        $book = Book::with(['author', 'category', 'subCategory', 'reviews.user'])
            ->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Calculate average rating and recommendations
        $averageRating = number_format($book->reviews->avg('rating') ?? 0, 2);
        $recommendationCount = $book->reviews->where('review', true)->count();
        $totalReviews = $book->reviews->count();
        $recommendationPercentage = number_format($totalReviews > 0 ? ($recommendationCount / $totalReviews) * 100 : 0, 2);

        // Get active discount for the main book
        $discount = $book->discountBooks()
            ->whereHas('discount', function ($query) {
                $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->latest()
            ->first();

        $discountPercentage = $discount ? optional($discount->discount)->discount_percentage : 0;
        $discountAmount = $discountPercentage > 0
            ? round($book->price_handbook * ($discountPercentage / 100), 2)
            : 0;
        $discountedPrice = $discountPercentage > 0
            ? round($book->price_handbook - $discountAmount, 2)
            : $book->price_handbook;

        // Fetch related books (same category)
        $relatedBooks = Book::where('category_id', $book->category_id)
            ->where('id', '!=', $book->id)
            ->with(['author', 'reviews', 'discountBooks.discount'])
            ->limit(5)
            ->get()
            ->map(function ($relatedBook) {
                $relatedAverageRating = number_format($relatedBook->reviews->avg('rating') ?? 0, 2);

                $relatedDiscount = $relatedBook->discountBooks()
                    ->whereHas('discount', function ($query) {
                        $query->where('start_date', '<=', now())
                            ->where('end_date', '>=', now());
                    })
                    ->latest()
                    ->first();

                $relatedDiscountPercentage = $relatedDiscount ? optional($relatedDiscount->discount)->discount_percentage : 0;
                $relatedDiscountAmount = $relatedDiscountPercentage > 0
                    ? round($relatedBook->price_handbook * ($relatedDiscountPercentage / 100), 2)
                    : 0;
                $relatedDiscountedPrice = $relatedDiscountPercentage > 0
                    ? round($relatedBook->price_handbook - $relatedDiscountAmount, 2)
                    : $relatedBook->price_handbook;

                return [
                    'id' => $relatedBook->id,
                    'title' => $relatedBook->title,
                    'description' => $relatedBook->description,
                    'cover_path' => $relatedBook->cover_path,
                    'author' => $relatedBook->author->name ?? 'Unknown',
                    'original_price' => $relatedBook->price_handbook,
                    'discount_percentage' => $relatedDiscountPercentage,
                    'discount_amount' => $relatedDiscountAmount,
                    'discounted_price' => $relatedDiscountedPrice,
                    'averageRating' => $relatedAverageRating,
                ];
            });

        return response()->json([
            'message' => 'Book retrieved successfully',
            'bookDetails' => [
                'book' => $book,
                'original_price' => $book->price_handbook,
                'discount' => $discount,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'discounted_price' => $discountedPrice,
                'averageRating' => $averageRating,
                'recommendationPercentage' => $recommendationPercentage,
                'recommendationCount' => $recommendationCount,
                'totalReviews' => $totalReviews,
                'relatedBooks' => $relatedBooks
            ]
        ], 200);
    }



    // public function update(Request $request, $id)
    // {
    //     // Validate the incoming request
    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'author_id' => 'required|integer|exists:authors,id',
    //         'category_id' => 'required|integer|exists:categories,id',
    //         'publisher' => 'nullable|string|max:255',
    //         'publish_date' => 'nullable|date',
    //         'pages' => 'nullable|integer',
    //         'dimensions' => 'nullable|string',
    //         'language' => 'nullable|string|max:255',
    //         'ean' => 'nullable|string|max:255',
    //         'price_handbook' => 'nullable|numeric|required_if:type,handbook',
    //     ]);

    //     // Find the book by ID
    //     $book = Book::findOrFail($id);
    //     $book->fill($validated); // Fill the validated fields

    //     // Check if a new cover image is uploaded
    //     if ($request->hasFile('cover_path') && $request->file('cover_path')->isValid()) {
    //         try {
    //             // Delete the old cover image from Cloudinary if it exists
    //             if ($book->cover_path) {
    //                 $uploadApi = new UploadApi();
    //                 $publicId = basename(parse_url($book->cover_path, PHP_URL_PATH), '.' . pathinfo($book->cover_path, PATHINFO_EXTENSION));
    //                 $uploadApi->destroy($publicId); // Delete the old image from Cloudinary
    //             }

    //             // Upload the new cover image to Cloudinary
    //             $imageFile = $request->file('cover_path');
    //             $uploadApi = new UploadApi();
    //             $uploadedFile = $uploadApi->upload($imageFile->getRealPath(), [
    //                 'folder' => 'imageBooks', // Set the folder where images should be uploaded
    //             ]);

    //             // Save the new image URL in the book record
    //             $book->cover_path = $uploadedFile['secure_url'];
    //         } catch (\Exception $e) {
    //             return response()->json([
    //                 'message' => 'Image upload failed',
    //                 'error' => $e->getMessage(),
    //             ], 500);
    //         }
    //     }

    //     // Save the updated book data
    //     $book->save();

    //     return response()->json([
    //         'message' => 'Book updated successfully',
    //         'book' => $book, // Return the updated book data
    //     ]);
    // }











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
        $dateThreshold = Carbon::now()->subDays(30);

        // Retrieve books created after the threshold date
        $books = Book::where('created_at', '>=', $dateThreshold)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'New books retrieved successfully.',
            'data' => $books,
        ]);
    }
}
