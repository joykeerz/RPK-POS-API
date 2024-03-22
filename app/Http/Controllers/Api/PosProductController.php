<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\PosInventory;
use App\Models\PosProduct;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PosProductController extends Controller
{
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

    public function createCompleteProduct(Request $request)
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
            'product_image' => 'required|image|mimes:jpeg,png,jpg|max:10000',
            'quantity' => 'required',
            'price' => 'required',
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product code harus di isi',
            'product_category_id.required' => 'product category harus di isi',
            'product_image.required' => 'product image harus di isi',
            'product_image.image' => 'product image harus berupa gambar',
            'product_image.mimes' => 'product image harus berupa gambar dengan format jpeg, png, jpg',
            'product_image.max' => 'product image maksimal 10MB',
            'quantity.required' => 'product quantity harus di isi',
            'price.required' => 'product price harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $filePath = 'none';
        if ($request->hasFile('product_image')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-product-image';
            $image = $request->file('product_image');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'product_image',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePath = $responseData['path'];
        }

        $product = new PosProduct();
        $product->profile_id = $profileId;
        $product->category_id = $request->product_category_id;
        $product->product_code = $request->product_code;
        $product->product_name = $request->product_name;
        $product->product_desc = $request->product_desc ?? 'tidak ada';
        $product->product_image = $filePath ?? 'images/pos/products/default.png';
        $product->save();

        $discountId = Discount::where('profile_id', $profileId)->where('discount_name', 'Tidak Diskon')->first();
        $inventory = new PosInventory();
        $inventory->product_id = $product->id;
        $inventory->discount_id = $discountId->id;
        $inventory->quantity = $request->quantity;
        $inventory->price = $request->price;
        $inventory->save();

        if (!$product || !$inventory) {
            return response()->json([
                'error' => "failed to create product"
            ], 500);
        }

        $response = Produk::with(['posInventory'])->where('id', $product->id)->first();
        return response()->json([
            'product_id' => $product->id,
            'inventory_id' => $inventory->id,
        ], 200);
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
            'product_image' => 'required|image|mimes:jpeg,png,jpg|max:10000',
            'quantity' => 'required',
            'price' => 'required',
            'discount_id' => 'required'
        ], [
            'product_name.required' => 'product name harus di isi',
            'product_code.required' => 'product code harus di isi',
            'product_category_id.required' => 'product category harus di isi',
            'product_image.required' => 'product image harus di isi',
            'product_image.image' => 'product image harus berupa gambar',
            'product_image.mimes' => 'product image harus berupa gambar dengan format jpeg, png, jpg',
            'product_image.max' => 'product image maksimal 10MB',
            'quantity.required' => 'product quantity harus di isi',
            'price.required' => 'product price harus di isi',
            'discount.required' => 'discount harus di isi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $filePath = 'none';
        if ($request->hasFile('product_image')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-product-image';
            $image = $request->file('product_image');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'product_image',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePath = $responseData['path'];
        }

        $product = PosProduct::where('id', $productId)->first();
        $product->category_id = $request->product_category_id;
        $product->product_code = $request->product_code;
        $product->product_name = $request->product_name;
        $product->product_desc = $request->product_desc ?? 'tidak ada';
        $product->product_image = $filePath ?? 'images/pos/products/default.png';
        $product->save();

        $inventory = PosInventory::where('product_id', $product->id)->first();
        $inventory->discount_id = $request->discount_id;
        $inventory->quantity = $request->quantity;
        $inventory->price = $request->price;
        $inventory->save();

        if (!$product || !$inventory) {
            return response()->json([
                'error' => "failed to update product"
            ], 500);
        }

        return response()->json([
            'product' => $product,
            'inventory' => $inventory
        ], 200);
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
