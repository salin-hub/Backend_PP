<?php

namespace App\Http\Controllers;

use App\Models\CouponUsage;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;

class CouponUsageController extends Controller
{
    // Store coupon usage
    public function store(Request $request)
{
    // Validate request
    $request->validate([
        'coupon_id' => 'required|exists:coupons,id',
        'users_id' => 'required|exists:users,id',
        'used_date' => 'required|date',
    ]);

    try {
        // Create coupon usage
        $couponUsage = CouponUsage::create([
            'coupon_id' => $request->coupon_id,
            'users_id' => $request->users_id,
            'used_date' => $request->used_date,
        ]);

        // Return success message and created coupon usage in JSON format
        return response()->json([
            'success' => true,
            'message' => 'Coupon usage created successfully!',
            'data' => $couponUsage
        ], 201);
    } catch (\Exception $e) {
        // Return error message in case of exception
        return response()->json([
            'success' => false,
            'message' => 'Error creating coupon usage.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Get all coupon usages
    public function index()
    {
        $couponUsages = CouponUsage::with('coupon', 'customer')->get();
        return response()->json($couponUsages);
    }
}
