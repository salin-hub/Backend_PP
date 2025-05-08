<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // Store a new coupon
    public function store(Request $request)
    {
        // Validate incoming request data
        $validated = $request->validate([
            'coupon_code' => 'required|string|unique:coupons,coupon_code',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'required|integer|min:1',
            'minimum_order_value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        // If validation passes, create a new coupon
        try {
            $coupon = Coupon::create([
                'coupon_code' => $request->coupon_code,
                'discount_percentage' => $request->discount_percentage,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'usage_limit' => $request->usage_limit,
                'minimum_order_value' => $request->minimum_order_value,
                'description' => $request->description,
            ]);

            // Return success message with created coupon data
            return response()->json([
                'message' => 'Coupon created successfully!',
                'coupon' => $coupon
            ], 201);
        } catch (\Exception $e) {
            // Return error message in case of exception
            return response()->json([
                'message' => 'Error creating coupon.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllCoupons(Request $request)
{
    try {

        // Retrieve paginated coupons
        $coupons = Coupon::all();

        // Return a structured JSON response
        return response()->json([
            'success' => true,
            'message' => 'Coupons retrieved successfully!',
            'coupons' => $coupons,
        ], 200);

    } catch (\Exception $e) {
        // Return error message in case of an exception
        return response()->json([
            'success' => false,
            'message' => 'Error fetching coupons.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}
