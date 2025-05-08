<?php
namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    // Display a list of all discounts
    public function index()
    {
        try{
        $discounts = Discount::all();
        return response()->json([
            'success' => true,
            'message' => 'Retrieved data is Success!',
            'discount_List'=>$discounts,
            ]
        ,201);
        }catch(\Exception $e){
            return response()->json([
               'message'=>'index is error!!!!',
                'error' => $e->getMessage()
            ]);
        }
       
    }

    // Store a new discount
    public function store(Request $request)
    {
        try{
        $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $discount = Discount::create($request->all());
        return response()->json($discount, 201);
    }
    catch (\Exception $e) {
        return response()->json([
            'message'=>'create is error!!!!',
            'error' => $e->getMessage()
        ],500);
    }
}

    // Show a specific discount
    public function show($id)
    {
        try{
        $discount = Discount::findOrFail($id);
        return response()->json([
            'message' => 'Retrieved data is Success!',
            'discount' => $discount
        ],201);
        }
        catch (\Exception $e) {
            return response()->json([
               'message'=>'show is error!!!!',
                'error' => $e->getMessage()
            ],500);
        }
    }

    // Update a discount
    public function update(Request $request, $id)
{
    try {
        $discount = Discount::findOrFail($id);

        $request->validate([
            'discount_percentage' => 'numeric|min:0|max:100',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date',
            'description' => 'nullable|string',
        ]);

        $discount->update($request->all());

        return response()->json($discount);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error updating discount',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Delete a discount
    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();
        return response()->json(['message' => 'Discount deleted successfully']);
    }
}
