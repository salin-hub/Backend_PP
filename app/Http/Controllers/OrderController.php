<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function createOrder(Request $request)
{
    Log::info('Incoming Order Data:', $request->all());

    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'items' => 'required|array',
        'items.*.books_id' => 'required|exists:books,id',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.price' => 'required|numeric|min:0',
    ]);

    Log::info('Validated Order Data:', $validated);

    $groupedItems = collect($validated['items'])->groupBy('books_id')->map(function ($group) {
        $firstItem = $group->first();
        return [
            'books_id' => $firstItem['books_id'],
            'quantity' => $group->sum('quantity'),
            'price' => $firstItem['price'],
        ];
    })->values();

    $totalPrice = $groupedItems->sum(function ($item) {
        return $item['quantity'] * $item['price'];
    });

    $order = Order::create([
        'user_id' => $validated['user_id'],
        'total_price' => $totalPrice,
    ]);

    foreach ($groupedItems as $item) {
        // Create order items
        OrderItem::create([
            'order_id' => $order->id,
            'books_id' => $item['books_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'status' => 'pending',
        ]);

        // Update stock
        $book = Book::find($item['books_id']);
        if ($book) {
            if ($book->quantity >= $item['quantity']) {
                $book->quantity -= $item['quantity'];
                $book->save();
            } else {
                // Optional: handle case if not enough stock
                Log::warning("Not enough stock for book ID {$item['books_id']}. Requested: {$item['quantity']}, Available: {$book->quantity}");
                return response()->json(['message' => "Not enough stock for book '{$book->title}'."], 400);
            }
        }
    }

    // Clean up cart
    foreach ($groupedItems as $item) {
        Cart::where('user_id', $validated['user_id'])
            ->where('book_id', $item['books_id'])
            ->delete();
    }

    return response()->json(['message' => 'Order created successfully!', 'order' => $order], 201);
}


    public function getAllOrders()
    {
        try {
            // Retrieve orders with items and the associated user's name (username)
            $orders = Order::with('items.book.author', 'user') // Assuming 'user' is the relationship name for the user in the Order model
                ->get()
                ->map(function ($order) {
                    // Add total book quantity to each order
                    $order->total_books = $order->items->sum('quantity');

                    // Add username to the order
                    $order->username = $order->user ? $order->user->name : 'Unknown User'; // Adjust 'name' based on your actual User model field

                    return $order;
                });

            return response()->json([
                'orders' => $orders,
            ], 200); // 200 OK status code
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching orders: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrdersByUserId($userId)
    {
        $this->authorize('view-user-orders');

        $orders = Order::where('user_id', $userId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'User orders fetched successfully!',
            'orders' => $orders
        ]);
    }
    public function getUserOrders($userId)
{
    try {
        // Fetch orders and eager load book + discount relationships
        $orders = Order::where('user_id', $userId)
            ->with(['items.book.discountBooks.discount'])
            ->get();

        // Process each order item
        $orders->each(function ($order) {
            $order->items->each(function ($item) {
                $book = $item->book;

                if ($book) {
                    // Base price
                    $originalPrice = $book->price_handbook;

                    // Try to get discount
                    $discount = $book->discountBooks->firstWhere('discount_id', '!=', null);
                    $discountPercentage = $discount ? optional($discount->discount)->discount_percentage : 0;

                    // Calculate discounted price
                    $discountedPrice = $originalPrice;
                    if ($discountPercentage > 0) {
                        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));
                    }

                    // Final unit price and total price
                    $unitPrice = $discountedPrice;
                    $totalPrice = $unitPrice * $item->quantity;

                    // Attach to item
                    $item->original_price = $originalPrice;
                    $item->discounted_price = $discountedPrice;
                    $item->discount_percentage = $discountPercentage;
                    $item->unit_price = $unitPrice;
                    $item->calculated_price = $totalPrice;
                }
            });
        });

        // Return response
        return response()->json([
            'message' => 'Orders fetched successfully!',
            'orders' => $orders
        ]);
    } catch (\Exception $e) {
        Log::error('Error fetching orders: ' . $e->getMessage());
        return response()->json([
            'message' => 'An error occurred while fetching orders.'
        ], 500);
    }
}



    // public function update(Request $request, $id)
    // {
    //     $author = Author::find($id);

    //     if (!$author) {
    //         return response()->json(['message' => 'Author not found'], 404);
    //     }

    //     $request->validate([
    //         'name' => 'sometimes|required|string|max:255',
    //         'email' => 'sometimes|required|email|unique:authors,email,' . $id,
    //         'description' => 'nullable|string',
    //         'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     // Update author details
    //     $author->name = $request->get('name', $author->name);
    //     $author->email = $request->get('email', $author->email);
    //     $author->description = $request->get('description', $author->description);

    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('authors', 'public');
    //         $author->image = $imagePath;
    //     }

    //     $author->save();

    //     return response()->json(['message' => 'Author updated successfully!', 'author' => $author], 200);
    // }

    public function getTotalOrderCount()
    {
        try {
            $totalOrders = OrderItem::count();

            return response()->json([
                'total_orders' => $totalOrders,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching total order count: ' . $e->getMessage()
            ], 500);
        }
    }
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'message' => 'nullable|string|max:255',
        ]);
        $orderItem = OrderItem::find($id);
        if (!$orderItem) {
            return response()->json(['message' => 'Order item not found'], 404);
        }
        $orderItem->update([
            'status' => $request->status,
            'message' => $request->message,
        ]);
        return response()->json(['message' => 'Order status updated successfully!', 'order_item' => $orderItem], 200);
    }

    public function getBestsellers(Request $request)
{
    try {
        $startDate = $request->input('start_date', now()->subMonth()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        $categoryId = $request->input('category_id');
        $limit = $request->input('limit', 10);

        // Filter OrderItems within date range
        $query = OrderItem::whereBetween('created_at', [$startDate, $endDate]);

        if ($categoryId) {
            $query->whereHas('book.category', function ($query) use ($categoryId) {
                $query->where('id', $categoryId);
            });
        }

        // Get bestselling book IDs with total sales
        $bestsellers = $query->select('books_id')
            ->selectRaw('SUM(quantity) as total_sales')
            ->groupBy('books_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        $bookIds = $bestsellers->pluck('books_id');

        // Fetch books with related data
        $books = Book::with(['author', 'category', 'discountBooks.discount'])
            ->whereIn('id', $bookIds)
            ->get()
            ->keyBy('id');

        // Fetch average ratings for those books
        $ratings = Review::select('book_id')
            ->selectRaw('AVG(rating) as avg_rating')
            ->whereIn('book_id', $bookIds)
            ->groupBy('book_id')
            ->get()
            ->keyBy('book_id');

        // Build response list
        $bestsellers = $bestsellers->map(function ($item) use ($books, $ratings) {
            $book = $books[$item->books_id] ?? null;
            if (!$book) return null;

            // Active discount (if any)
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

            $averageRating = round($ratings[$book->id]->avg_rating ?? 0, 1);

            return [
                'id' => $book->id,
                'title' => $book->title,
                'cover_path' => $book->cover_path,
                'description' => $book->description,
                'price_handbook' => $book->price_handbook,
                'discount_id' => $discount ? optional($discount->discount)->id : null,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'discounted_price' => $discountedPrice,
                'author' => $book->author ? $book->author->name : 'Unknown',
                'total_sales' => $item->total_sales,
                'rating' => $averageRating,
            ];
        })->filter();

        return response()->json([
            'bestsellers' => $bestsellers->values(),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error fetching bestsellers: ' . $e->getMessage()
        ], 500);
    }
}
}
