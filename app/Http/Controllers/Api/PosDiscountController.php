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
                'error' => "there's no data yet"
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
