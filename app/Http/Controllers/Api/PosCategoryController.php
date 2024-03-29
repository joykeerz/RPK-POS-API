<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosCategoryController extends Controller
{
    public function getUserCategory()
    {
        $category = PosCategory::where('profile_id', Auth::user()->posProfile->id)->get();
        return $category;
        if (empty($category)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($category, 200);
    }

    public function getSingleCategory($id)
    {
        $category = PosCategory::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (empty($category)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        return response()->json($category, 200);
    }

    public function createCategory(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
        ], [
            'category_name.required' => 'category name harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $category = new PosCategory();
        $category->profile_id = Auth::user()->posProfile->id;
        $category->category_name = $request->category_name;
        $category->category_desc = $request->category_desc;
        $category->save();

        if (!$category) {
            return response()->json([
                'error' => "failed to create category"
            ], 500);
        }
        return response()->json($category, 200);
    }

    public function updateCategory(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'category_name' => 'required',
        ], [
            'category_name.required' => 'category name harus di isi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $category = PosCategory::where('profile_id', Auth::user()->posProfile->id)->where('id', $request->id)->first();
        if (empty($category)) {
            return response()->json([
                'error' => "there's no data yet"
            ], 404);
        };

        $category->category_name = $request->category_name;
        $category->category_desc = $request->category_desc;
        $category->save();

        if (!$category) {
            return response()->json([
                'error' => "failed to update category"
            ], 500);
        }
        return response()->json($category, 200);
    }
}
