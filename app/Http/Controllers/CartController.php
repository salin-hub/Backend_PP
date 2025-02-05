<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1', // Ensure a valid quantity
        ]);

        // Check if the book is already in the cart
        $cartItem = Cart::where('user_id', $request->user_id)
            ->where('book_id', $request->book_id)
            ->first();

        if ($cartItem) {
            // If the book already exists in the cart, update the quantity
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            // If the book is not in the cart, create a new cart item
            Cart::create([
                'user_id' => $request->user_id,
                'book_id' => $request->book_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Book added to cart successfully!'], 200);
    }
    public function viewCart($userId)
    {
        $cartItems = Cart::where('user_id', $userId)
            ->with('book') // Assuming you have a relationship defined in the Cart model to fetch book details
            ->get();

        return response()->json(['cartItems' => $cartItems]);
    }
    public function deleteCartItem(Request $request, $id)
    {
        // Find the cart item by ID
        $cartItem = Cart::find($id);

        // Check if the cart item exists
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        // Delete the cart item
        $cartItem->delete();

        return response()->json(['message' => 'Cart item deleted successfully.'], 200);
    }
    public function updateQuantity(Request $request, $id)
    {
        // Validate the new quantity
        $request->validate([
            'quantity' => 'required|integer|min:1', // Ensure the quantity is valid
        ]);

        // Find the cart item by ID
        $cartItem = Cart::find($id);

        // Check if the cart item exists
        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        // Update the quantity
        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['message' => 'Cart item quantity updated successfully.'], 200);
    }
}
