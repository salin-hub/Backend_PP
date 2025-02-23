<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
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

        $category = Category::create($request->all());

        return response()->json($category, 201);
    }

    // Get all categories
    public function getAllCategories()
    {
        try {
            $categories = Category::with('subcategories')->get();
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

    // Get a single category
    public function getCategory($id)
    {
        try {
            $category = Category::with(['books', 'subcategories'])->findOrFail($id);
            return response()->json(['category' => $category], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found', 'details' => $e->getMessage()], 404);
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
            $category = Category::findOrFail($id);
            $category->update($request->all());
            return response()->json($category, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found or update failed'], 404);
        }
    }

    // Delete a category
    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found or deletion failed'], 404);
        }
    }

    // Get Books by Category
    public function getBooksByCategory($categoryId)
    {
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

    // Create a SubCategory
    public function createSubCategory(Request $request, $categoryId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $category = Category::findOrFail($categoryId);
            $subCategory = $category->subcategories()->create(['name' => $request->name]);
            return response()->json($subCategory, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create subcategory', 'details' => $e->getMessage()], 500);
        }
    }

    // Get SubCategories by Category
    public function getSubCategories($categoryId)
    {
        try {
            $category = Category::with('subcategories')->findOrFail($categoryId);
            return response()->json(['subcategories' => $category->subcategories], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Category not found', 'details' => $e->getMessage()], 404);
        }
    }
    public function getAllSubCategories()
{
    try {
        // Fetch all subcategories
        $subcategories = SubCategory::all();

        // Return subcategories in the response
        return response()->json(['subcategories' => $subcategories], 200);
    } catch (\Exception $e) {
        // Return an error response in case of any failure
        return response()->json(['error' => 'Failed to retrieve subcategories', 'details' => $e->getMessage()], 500);
    }
}


    // Get a single SubCategory by ID
    public function getSubCategory($categoryId, $subCategoryId)
    {
        try {
            $subCategory = SubCategory::where('category_id', $categoryId)->findOrFail($subCategoryId);
            return response()->json(['subCategory' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Subcategory not found', 'details' => $e->getMessage()], 404);
        }
    }

    // Update a SubCategory
    public function updateSubCategory(Request $request, $categoryId, $subCategoryId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $subCategory = SubCategory::where('category_id', $categoryId)->findOrFail($subCategoryId);
            $subCategory->update($request->all());
            return response()->json(['subCategory' => $subCategory], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Subcategory update failed', 'details' => $e->getMessage()], 500);
        }
    }

    // Delete a SubCategory
    public function deleteSubCategory($categoryId, $subCategoryId)
    {
        try {
            $subCategory = SubCategory::where('category_id', $categoryId)->findOrFail($subCategoryId);
            $subCategory->delete();
            return response()->json(['message' => 'Subcategory deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Subcategory deletion failed', 'details' => $e->getMessage()], 500);
        }
    }
 

}
