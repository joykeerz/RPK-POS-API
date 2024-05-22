<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosInventory;
use App\Models\PosProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosInventoryController extends Controller
{
    //
    public function getUserInventory()
    {
        $profileId = Auth::user()->posProfile->id;
        $inventory = PosProduct::with(['posInventory', 'posCategory'])
            ->where('profile_id', $profileId)
            ->whereHas('posInventory')
            ->get();

        if ($inventory->isEmpty()) {
            return response()->json([
                'error' => "There's no data yet"
            ], 404);
        }

        return response()->json($inventory, 200);
    }


    public function getSingleInventory($id)
    {
        $profileId = Auth::user()->posProfile->id;
        $inventory = PosProduct::with(['posInventory', 'posCategory'])
            ->where('profile_id', $profileId)
            ->where('id', $id)
            ->first();

        if (empty($inventory)) {
            return response()->json([
                'error' => "there's no data for this id"
            ], 404);
        };

        return response()->json($inventory, 200);
    }

    public function createInventory(Request $request)
    {
        $profileId = Auth::user()->posProfile->id;
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'discount_id' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ], [
            'product_id.required' => 'product id harus di isi',
            'discount_id.required' => 'discount id harus di isi',
            'quantity.required' => 'product qty harus di isi',
            'price.required' => 'product price harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $inventory = PosInventory::create([
            'product_id' => $request->product_id,
            'discount_id' => $request->discount_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        if ($inventory) {
            return response()->json(
                $inventory,
                201
            );
        } else {
            return response()->json([
                'message' => 'failed create inventory',
            ], 400);
        }
    }

    public function updateInventory(Request $request, $id)
    {
        $profileId = Auth::user()->posProfile->id;
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ], [
            'product_id.required' => 'product id harus di isi',
            'quantity.required' => 'product qty harus di isi',
            'price.required' => 'product price harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $inventory = PosInventory::where('id', $id)->first();
        if (empty($inventory)) {
            return response()->json([
                'error' => "data not found"
            ], 404);
        };

        $inventory->product_id = $request->product_id;
        $inventory->quantity = $request->quantity;
        $inventory->price = $request->price;
        $inventory->save();

        if ($inventory) {
            return response()->json(
                $inventory,
                201
            );
        } else {
            return response()->json([
                'message' => 'failed update inventory',
            ], 400);
        }
    }

    public function deleteInventory($id)
    {
        $inventory = PosInventory::where('id', $id)->first();
        if (!$inventory) {
            return response()->json([
                'error' => "data not found"
            ], 404);
        };

        $inventory->delete();

        if ($inventory) {
            return response()->json([
                'message' => 'success delete inventory'
            ], 201);
        } else {
            return response()->json([
                'message' => 'failed delete inventory',
            ], 400);
        }
    }

    public function updateInventoryQuantity(Request $request, $id)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            // 'type' => 'required',
            'quantity' => 'required',
        ], [
            // 'type.required' => 'type harus di isi',
            'quantity.required' => 'quantity harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $inventory = PosInventory::findOrFail($id);

        if ($request->type == 'add' || $request->quantity > 0) {
            $inventory->increment('quantity', $request->quantity);
        } elseif (($request->type == 'reduce' && $inventory->quantity > $request->quantity) || ($inventory->quantity > $request->quantity && $request->quantity < 0)) {
            $inventory->decrement('quantity', $request->quantity);
        } else {
            return response()->json([
                'error' => "Invalid operation"
            ], 400);
        }

        return response()->json($inventory, 200);
    }
}
