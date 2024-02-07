<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosPromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $promo = Promo::where('profile_id', Auth::user()->posProfile->id)->get();
        if (empty($promo)) {
            return response()->json([
                'error' => "there's no promo yet in this account"
            ], 404);
        }
        return response()->json($promo, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'promo_name' => 'required',
            'promo_type' => 'required',
            'promo_value' => 'required',
            'promo_start' => 'required',
            'promo_end' => 'required',
            'is_active' => 'required',
        ], [
            'promo_name.required' => 'promo name harus di isi',
            'promo_type.required' => 'promo type harus di isi',
            'promo_value.required' => 'promo value harus di isi',
            'promo_start.required' => 'promo start harus di isi',
            'promo_end.required' => 'promo end harus di isi',
            'is_active.required' => 'promo status harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        if ($request->promo_start < now()) {
            return response()->json([
                'error' => "promo start date must be greater than today"
            ], 400);
        }

        $promo = new Promo();
        $promo->profile_id = Auth::user()->posProfile->id;
        $promo->promo_name = $request->promo_name;
        $promo->promo_type = $request->promo_type;
        $promo->promo_category = "User Defined";
        $promo->promo_value = $request->promo_value;
        $promo->promo_start = $request->promo_start;
        $promo->promo_end = $request->promo_end;
        $promo->is_active = $request->is_active;
        $promo->is_from_bulog = false;
        $promo->save();

        if (!$promo) {
            return response()->json([
                'error' => "failed to save promo"
            ], 400);
        }

        return response()->json($promo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $promo = Promo::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($promo)) {
            return response()->json([
                'error' => "promo not found"
            ], 404);
        }
        return response()->json($promo, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'promo_name' => 'required',
            'promo_type' => 'required',
            'promo_value' => 'required',
            'promo_start' => 'required',
            'promo_end' => 'required',
            'is_active' => 'required',
        ], [
            'promo_name.required' => 'promo name harus di isi',
            'promo_type.required' => 'promo type harus di isi',
            'promo_value.required' => 'promo value harus di isi',
            'promo_start.required' => 'promo start harus di isi',
            'promo_end.required' => 'promo end harus di isi',
            'is_active.required' => 'promo status harus di isi',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $promo = Promo::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();

        if (empty($promo)) {
            return response()->json([
                'error' => "promo not found"
            ], 404);
        }

        if ($request->promo_start < now()) {
            return response()->json([
                'error' => "promo start date must be greater than today"
            ], 400);
        }

        $promo->promo_name = $request->promo_name;
        $promo->promo_type = $request->promo_type;
        $promo->promo_value = $request->promo_value;
        $promo->promo_start = $request->promo_start;
        $promo->promo_end = $request->promo_end;
        $promo->is_active = $request->is_active;
        $promo->save();

        if (!$promo) {
            return response()->json([
                'error' => "failed to update promo"
            ], 400);
        }

        return response()->json($promo, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $promo = Promo::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($promo)) {
            return response()->json([
                'error' => "promo not found"
            ], 404);
        }
        $promo->delete();
        return response()->json([
            'message' => "promo deleted"
        ], 200);
    }
}
