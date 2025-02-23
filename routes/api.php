<?php

use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

// Public Routes
Route::get('subcategories', [CategoryController::class, 'getAllSubCategories']); 
Route::post('admin-login', [AuthController::class, 'loginadmin']);
Route::post('login', [AuthController::class, 'Login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('/books', [BookController::class, 'getAllBooks']);
Route::post('/favorite', [FavoriteController::class, 'addfavorite']);
Route::delete('/favorite', [FavoriteController::class, 'deleteFavorite']);
Route::post('/cart', [CartController::class, 'addToCart']); // Add to cart
Route::get('/cart/{userId}', [CartController::class, 'viewCart']); // View cart
Route::delete('/cart/{id}', [CartController::class, 'deleteCartItem']);
Route::put('/cart/update/{id}', [CartController::class, 'updateQuantity']);
Route::get('/book/{id}', [BookController::class, 'getBookById']);
Route::get('/categories/{id}/books', [CategoryController::class, 'getBooksByCategory']);
Route::get('/favorites/{userid}', [FavoriteController::class, 'list']);
Route::get('categories', [CategoryController::class, 'getAllCategories']);
Route::get('/books/search', [BookController::class, 'searchBooks']);
Route::get('/book/recommendations', [BookController::class, 'getBookRecommendations']);
Route::post('/orders', [OrderController::class, 'createOrder']);
Route::get('/users/{userId}/orders', [OrderController::class, 'getUserOrders']);
Route::get('/spasific_author/{id}', [AuthorController::class, 'specific_author']);
Route::get("/most_favorite", [FavoriteController::class, 'getMostFavoritedBooks']);
Route::post('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('getauthors', [AuthorController::class, 'getallauthors']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/account', [AuthController::class, 'getSpecificAccount']);
Route::get('/books/new', [BookController::class, 'getNewBooks']);
Route::get('/user/{userId}/favorites', [FavoriteController::class, 'getUserFavorites']);
Route::get('/orders', [OrderController::class, 'getAllOrders']);
Route::put('/order-items/{id}/status', [OrderController::class, 'updateOrderStatus']);
Route::get('/total-orders', [OrderController::class, 'getTotalOrderCount']);
Route::get('getauthors', [AuthorController::class, 'getallauthors'])->name('authors.index');
Route::get('/{id}', [CategoryController::class, 'getCategory']);
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'getAllCategories']);
    Route::put('/order-items/{id}/status', [OrderController::class, 'updateOrderStatus']);
    Route::middleware('auth:sanctum')->get('/accounts', [AuthController::class, 'getAllAccounts']);
    Route::get('/users/{id}', [AuthController::class, 'getAccount']);
    Route::put('/users/{id}', [AuthController::class, 'updateAccount']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteAccount']);
    Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::get('getauthors', [AuthorController::class, 'getallauthors'])->name('authors.index');
    Route::get('/authors/{id}', [AuthorController::class, 'show']);
    // Route to update an author by ID
    Route::put('authors/{id}', [AuthorController::class, 'update']);
    Route::get('{categoryId}/subcategories', [CategoryController::class, 'getSubCategories']); // Get all subcategories by category
    Route::get('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'getSubCategory']); // Get a specific subcategory by category
    
    Route::post('{categoryId}/subcategories', [CategoryController::class, 'createSubCategory']); // Create a new subcategory under a category
    Route::put('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'updateSubCategory']); // Update a subcategory under a category
    Route::delete('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'deleteSubCategory']); // Delete a subcategory under a category


    // Route to delete an author by ID
    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
    // Category Routes
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('getcategories', [CategoryController::class, 'getdata'])->name('categories.getdata');
    // Get a single category by ID
    Route::get('/categories/{id}', [CategoryController::class, 'getCategory']);

    // Update a category by ID
    Route::put('/categories/{id}', [CategoryController::class, 'update']);

    // Delete a category by ID
    Route::delete('/categories/{id}', [CategoryController::class, 'delete']);
    Route::get('getbooks', [BookController::class, 'getAllBooks'])->name('books.index');
    // Book Routes
    Route::post('books', [BookController::class, 'storeBook'])->name('ebooks.store');
    Route::get('/books/{id}', [BookController::class, 'getBookById']);   // Retrieve a single book by ID
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'deleteBook']); // Delete a book
    // Fetch all orders (requires authorization)
    Route::get('/orders', [OrderController::class, 'getAllOrders'])->middleware('auth:api');
});
Route::middleware('auth:sanctum', 'user')->group(function () {
    Route::get('/user-dashboard', [AuthController::class, 'userDashboard']);
});
Route::middleware('auth:sanctum')->post('/rate', [RatingController::class, 'rate']);