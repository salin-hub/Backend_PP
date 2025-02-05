<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        Log::info('Incoming Order Data:', $request->all());

        // Validate the request
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
            OrderItem::create([
                'order_id' => $order->id,
                'books_id' => $item['books_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'status' => 'pending',
            ]);
        }
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
            $orders = Order::where('user_id', $userId)
                ->with(['items.book'])
                ->get();

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
    
}
