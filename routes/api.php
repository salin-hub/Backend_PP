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
use App\Http\Controllers\CouponUsageController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DiscountBookController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\ReviewController;

Route::get('/accounts', [AuthController::class, 'getAllAccounts']);
Route::post('books', [BookController::class, 'storeBook'])->name('books.store');
Route::get('subcategories', [CategoryController::class, 'getCategoriesWithSubCategories']);
Route::put('/users/{id}', [AuthController::class, 'updateAccount']);
Route::get('/subcategories/{id}/books', [CategoryController::class, 'getSubCategoryById']);
Route::put('/books/{id}', [BookController::class, 'updateBook']);
// Route::get('/recommendations', [BookController::class, 'getBookRecommendations']);
Route::middleware('auth:sanctum')->get('/recommendations', [BookController::class, 'getBookRecommendations']);
// Public Routes
Route::post('/usecoupon', [CouponUsageController::class, 'store']);
Route::post('/addReview',[ReviewController::class, 'store']);
Route::get('/getBookDiscounts',[DiscountBookController::class,'getBookDiscounts']);
Route::get('/getReview/{id}',[ReviewController::class, 'show']);
Route::put('/updateReview/{id}',[ReviewController::class,'update']);
Route::delete('/deleteReview/{id}',[ReviewController::class,'destroy']);

Route::get('/books/search', [BookController::class, 'searchBooks']);
Route::get('/books/new', [BookController::class, 'getNewBooks']);
Route::get('/books/{id}', [BookController::class, 'getBookById']);
Route::delete('/deleteDiscount/{id}', [DiscountController::class, 'destroy']);
Route::put('/updateDiscount/{id}', [DiscountController::class, 'update']);
Route::get('/getDiscount', [DiscountController::class, 'Index']);
Route::delete('/deleteBookDiscount/{id}', [DiscountBookController::class, 'destroy']);
Route::get('/getDiscount/{id}', [DiscountController::class, 'show']);
// Route::post('/createDiscount',[DiscountController::class,'store']);
Route::post('/DiscountBook', [DiscountBookController::class, 'store']);
Route::get('/getcoupon', [couponController::class, 'getAllCoupons']);
Route::post('/addcoupong', [CouponController::class, 'store']);
Route::get('/getcoupong', [CouponUsageController::class, 'index']);

Route::get('/bestsellers', [OrderController::class, 'getBestsellers']);
Route::get('allsubcategories', [CategoryController::class, 'getAllSubCategories']);
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


Route::post('/orders', [OrderController::class, 'createOrder']);
Route::get('/users/{userId}/orders', [OrderController::class, 'getUserOrders']);
Route::get('/spasific_author/{id}', [AuthorController::class, 'specific_author']);
Route::get("/most_favorite", [FavoriteController::class, 'getMostFavoritedBooks']);
Route::post('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);
Route::get('getauthors', [AuthorController::class, 'getallauthors']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/account', [AuthController::class, 'getSpecificAccount']);
Route::get('/user/{userId}/favorites', [FavoriteController::class, 'getUserFavorites']);
Route::get('/orders', [OrderController::class, 'getAllOrders']);
Route::put('/order-items/{id}/status', [OrderController::class, 'updateOrderStatus']);
Route::get('/total-orders', [OrderController::class, 'getTotalOrderCount']);
Route::get('getauthors', [AuthorController::class, 'getallauthors'])->name('authors.index');
Route::get('/{id}', [CategoryController::class, 'getCategory']);
Route::put('/users/{id}/status', [AuthController::class, 'toggleStatus']);
Route::get('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'getSubCategory']);
Route::get('{categoryId}/subcategories', [CategoryController::class, 'getSubCategoryById']);

// Route for admin controller 
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('/accounts', [AuthController::class, 'getAllAccounts']);
    Route::put('/users/{id}/status', [AuthController::class, 'toggleStatus']);
    Route::get('/categories', [CategoryController::class, 'getAllCategories']);
    Route::put('/order-items/{id}/status', [OrderController::class, 'updateOrderStatus']);
    
    Route::get('/users/{id}', [AuthController::class, 'getAccount']);
    Route::put('/users/{id}', [AuthController::class, 'updateAccount']);
    Route::delete('/users/{id}', [AuthController::class, 'deleteAccount']);
    Route::post('authors', [AuthorController::class, 'store'])->name('authors.store');
    Route::get('getauthors', [AuthorController::class, 'getallauthors'])->name('authors.index');
    Route::get('/authors/{id}', [AuthorController::class, 'show']);

    Route::put('authors/{id}', [AuthorController::class, 'update']);
    Route::get('subcategories', [CategoryController::class, 'getCategoriesWithSubCategories']);
    Route::get('{categoryId}/subcategories', [CategoryController::class, 'getSubCategories']); // Get all subcategories by category
    Route::get('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'getSubCategory']); // Get a specific subcategory by category

    Route::post('{categoryId}/subcategories', [CategoryController::class, 'createSubCategory']); // Create a new subcategory under a category
    Route::put('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'updateSubCategory']); // Update a subcategory under a category
    Route::delete('{categoryId}/subcategories/{subCategoryId}', [CategoryController::class, 'deleteSubCategory']); // Delete a subcategory under a category

    Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('getcategories', [CategoryController::class, 'getdata'])->name('categories.getdata');
    Route::get('/categories/{id}', [CategoryController::class, 'getCategory']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'delete']);
    Route::get('getbooks', [BookController::class, 'getAllBooks'])->name('books.index');

    Route::post('books', [BookController::class, 'storeBook'])->name('ebooks.store');
    Route::get('/books/{id}', [BookController::class, 'getBookById']);
    Route::put('/books/{id}', [BookController::class, 'updateBook']);
    Route::delete('/books/{id}', [BookController::class, 'deleteBook']);
    Route::get('/orders', [OrderController::class, 'getAllOrders'])->middleware('auth:api');

    Route::post('/createDiscount', [DiscountController::class, 'store']);
    Route::get('/books/new', [BookController::class, 'getNewBooks']);
    Route::get('/books/{id}', [BookController::class, 'getBookById']);
    Route::delete('/deleteDiscount/{id}', [DiscountController::class, 'destroy']);
    Route::put('/updateDiscount/{id}', [DiscountController::class, 'update']);
    Route::get('/getDiscount', [DiscountController::class, 'Index']);
    Route::post('/DiscountBook', [DiscountBookController::class, 'store']);
});

// Route for User cotroller 
Route::middleware('auth:sanctum', 'user')->group(function () {
    Route::get('/user-dashboard', [AuthController::class, 'userDashboard']);
});
Route::middleware('auth:sanctum')->post('/rate', [RatingController::class, 'rate']);
