<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Discount;
use App\Models\DiscountBook;
use Illuminate\Http\Request;

class DiscountBookController extends Controller
{
    // Display all discount-book relationships
    public function index()
    {
        $discountBooks = DiscountBook::with(['book', 'discount'])->get();
        return response()->json($discountBooks);
    }

    // Store a new discount-book relationship
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'discount_id' => 'required|exists:discounts,discount_id'
        ]);

        $discountBook = DiscountBook::create([
            'book_id' => $request->book_id,
            'discount_id' => $request->discount_id,
        ]);

        return response()->json([
            'message' => 'Discount applied successfully',
            'discount_book' => $discountBook
        ], 201);
    }

    // Show a specific discount-book entry
    public function show($id)
    {
        $discountBook = DiscountBook::with(['book', 'discount'])->findOrFail($id);
        return response()->json($discountBook);
    }

    // Update a discount-book entry
    public function update(Request $request, $id)
    {
        $discountBook = DiscountBook::findOrFail($id);

        $request->validate([
            'book_id' => 'exists:books,id',
            'discount_id' => 'exists:discounts,id',
            'final_price' => 'numeric|min:0',
        ]);

        $discountBook->update($request->all());
        return response()->json($discountBook);
    }
    public function destroy($id)
    {
        // Find the book by ID
        $book = Book::find($id);

        // If book not found, return error response
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Check if the book has any discounts
        $discounts = $book->discountBooks()->get();

        if ($discounts->isEmpty()) {
            return response()->json(['message' => 'No discount found for this book'], 404);
        }

        // Delete all associated discounts
        $book->discountBooks()->delete();

        return response()->json(['message' => 'Discount removed successfully']);
    }
    public function getBookDiscounts()
    {
        $booksWithDiscounts = Book::whereHas('discountBooks')->with('discountBooks.discount')->get()->map(function ($book) {

            // Get the latest valid discount for the book
            $discount = $book->discountBooks()
                ->whereHas('discount', function ($query) {
                    $query->where('start_date', '<=', now())
                        ->where('end_date', '>=', now());
                })
                ->latest()
                ->first();

            // If there is a valid discount, calculate the discounted price
            if ($discount) {
                $discountPercentage = optional($discount->discount)->discount_percentage;
                $discountAmount = $book->price_handbook * ($discountPercentage / 100);
                $discountedPrice = round($book->price_handbook - $discountAmount, 2);

                return [
                    'book_id' => $book->id,
                    'book_title' => $book->title,
                    'book_image' => $book->cover_path,
                    'book_description' => $book->description,
                    'original_price' => $book->price_handbook,
                    'discount_id' => $discount->id,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => round($discountAmount, 2),
                    'discounted_price' => $discountedPrice
                ];
            }

            // If no valid discount exists, return the book's original price and no discount details
            return [
                'book_id' => $book->id,
                'book_title' => $book->title,
                'book_image' => $book->cover_path,
                'book_description' => $book->description,
                'original_price' => $book->price_handbook,
                'discount_id' => null,
                'discount_percentage' => null,
                'discount_amount' => null,
                'original_price' => $book->price_handbook,
                'discount_id' => null,
                'discount_percentage' => null,
                'discount_amount' => null,
                'discounted_price' => $book->price_handbook
            ];
        });

        return response()->json([
            "message" => "Received book discounts are available",
            "Discounts" => $booksWithDiscounts,
        ]);
    }
}
