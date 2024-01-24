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
            ->get();

        if (empty($inventory)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($inventory, 200);
    }

    public function getUserProducts()
    {
        $profileId = Auth::user()->posProfile->id;
        $products  = PosProduct::with(['posCategory'])
            ->where('profile_id', $profileId)
            ->get();

        if (empty($products)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($products, 200);
    }

    public function createSingleProduct(Request $request)
    {
        $profileId = Auth::user()->posProfile->id;
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required',
            'product_category_id' => 'required',
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product code harus di isi',
            'product_category_id.required' => 'product category harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $product = PosProduct::create([
            'profile_id' => $profileId,
            'category_id' => $request->product_category_id,
            'product_code' => $request->product_code,
            'product_name' => $request->product_name,
            'product_desc' => $request->product_desc ?? 'tidak ada',
            'product_image' => $request->product_image ?? 'default.png',
        ]);

        if (!$product) {
            return response()->json([
                'error' => "failed to create product"
            ], 500);
        }

        return response()->json($product, 200);
    }

    public function updateSingleProduct(Request $request, $productId)
    {
        $profileId = Auth::user()->posProfile->id;
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'product_code' => 'required',
            'product_category_id' => 'required',
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product code harus di isi',
            'product_category_id.required' => 'product category harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $product = PosProduct::where('id', $productId)->first();
        $product->profile_id = $profileId;
        $product->category_id = $request->product_category_id;
        $product->product_code = $request->product_code;
        $product->product_name = $request->product_name;
        $product->product_desc = $request->product_desc ?? 'tidak ada';
        $product->product_image = $request->product_image ?? 'default.png';
        $product->save();

        // $product = PosProduct::where('id', $productId)->update([
        //     'profile_id' => $profileId,
        //     'category_id' => $request->product_category_id,
        //     'product_code' => $request->product_code,
        //     'product_name' => $request->product_name,
        //     'product_desc' => $request->product_desc ?? 'tidak ada',
        //     'product_image' => $request->product_image ?? 'default.png',
        // ]);

        if (!$product) {
            return response()->json([
                'error' => "failed to update product"
            ], 500);
        }

        return response()->json($product, 200);
    }

    public function getSingleProduct($productId)
    {
        $profileId = Auth::user()->posProfile->id;
        $product = PosProduct::with(['posCategory'])
            ->where('profile_id', $profileId)
            ->where('id', $productId)
            ->first();

        if (empty($product)) {
            return response()->json([
                'error' => "Product not found"
            ], 404);
        };

        return response()->json($product, 200);
    }

    public function deleteSingelProduct($productId)
    {
        $product = PosProduct::where('id', $productId)->delete();

        if (!$product) {
            return response()->json([
                'error' => "failed to delete product"
            ], 500);
        }

        return response()->json([
            'message' => "success to delete product"
        ], 200);
    }
}
