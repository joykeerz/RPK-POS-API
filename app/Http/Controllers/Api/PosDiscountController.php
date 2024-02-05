<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosDiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $discount = Discount::where('profile_id', Auth::user()->posProfile->id)->get();
        if (empty($discount)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        }

        return response()->json($discount, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'discount_name' => 'required',
            'discount_type' => 'required',
            'discount_value' => 'required',
        ], [
            'discount_name.required' => 'discount name harus di isi',
            'discount_type.required' => 'discount type harus di isi',
            'discount_value.required' => 'discount value harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        // $discount = Discount::create([
        //     'profile_id' => Auth::user()->posProfile->id,
        //     'discount_name' => $request->discount_name,
        //     'discount_type' => $request->discount_type,
        //     'discount_value' => $request->discount_value,
        // ]);

        $discount = new Discount();
        $discount->profile_id = Auth::user()->posProfile->id;
        $discount->discount_name = $request->discount_name;
        $discount->discount_type = $request->discount_type;
        $discount->discount_value = $request->discount_value;
        $discount->save();

        if (!$discount) {
            return response()->json([
                'error' => "failed to create discount data"
            ], 400);
        }

        return response()->json($discount, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $discount = Discount::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($discount)) {
            return response()->json([
                'error' => "discount data not found"
            ], 404);
        }
        return response()->json($discount, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'discount_name' => 'required',
            'discount_type' => 'required',
            'discount_value' => 'required',
            'is_active' => 'required',
        ], [
            'discount_name.required' => 'discount name harus di isi',
            'discount_type.required' => 'discount type harus di isi',
            'discount_value.required' => 'discount value harus di isi',
            'is_active.required' => 'discount is active harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $discount = Discount::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($discount)) {
            return response()->json([
                'error' => "discount data not found"
            ], 404);
        }

        $discount->discount_name = $request->discount_name;
        $discount->discount_type = $request->discount_type;
        $discount->discount_value = $request->discount_value;
        $discount->is_active = $request->is_active;
        $discount->save();

        return response()->json($discount, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $discount = Discount::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($discount)) {
            return response()->json([
                'error' => "discount data not found"
            ], 404);
        }
        $discount->delete();
        return response()->json([
            'message' => "discount data deleted"
        ], 200);
    }
}
