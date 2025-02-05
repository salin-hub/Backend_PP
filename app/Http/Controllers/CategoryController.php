<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Create a new category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        // Create a new category using validated data
        $category = Category::create($request->all());

        return response()->json($category, 201); // Return created category with HTTP 201 status
    }

    // Get all categories
    public function getData()
    {
        try {
            $categories = Category::all(); // Retrieve all categories from the database
            return response()->json($categories, 200); // Return categories with HTTP 200 status
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500); // Handle errors
        }
    }
    // Get all categories
    public function getAllCategories()
    {
        try {
            $categories = Category::all();
            return response()->json([
                'message' => 'Categories retrieved successfully',
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve categories',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCategory($id)
    {
        try {
            // Eager load books for the category
            $category = Category::with('books')->findOrFail($id);

            return response()->json([
                'category' => $category, // Return category with its books
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Category not found',
                'details' => $e->getMessage(),
            ], 404); // Handle not found error
        }
    }

    // Update a category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:255',
        ]);

        try {
            $category = Category::findOrFail($id); // Find category by ID
            $category->update($request->all()); // Update category with new data
            return response()->json($category, 200); // Return updated category with HTTP 200 status
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found or update failed'], 404); // Handle error
        }
    }

    // Delete a category
    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id); // Find category by ID
            $category->delete(); // Delete category from the database
            return response()->json(['message' => 'Category deleted successfully'], 200); // Return success message
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found or deletion failed'], 404); // Handle error
        }
    }
    public function getBooksByCategory($categoryId)
    {
        // Find the category and load related books
        $category = Category::with('books')->find($categoryId);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'message' => 'Books retrieved successfully',
            'category' => $category->name,
            'books' => $category->books,
        ], 200);
    }
}
