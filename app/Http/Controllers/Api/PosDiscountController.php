<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
