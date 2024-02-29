<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethod = PosPayment::where('profile_id', Auth::user()->posProfile->id)->get();
        if (!$paymentMethod || $paymentMethod->count() < 1) {
            return response()->json([
                'message' => 'No payment method found'
            ], 404);
        }
        return response()->json($paymentMethod, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        $validator = Validator::make([
            'method_name' => 'required|string',
            'payment_type' => 'required|string',
            'payment_info' => 'required|string',
        ], [
            'method_name.required' => 'method name is required',
            'payment_type.required' => 'Payment type is required',
            'payment_info.required' => 'Payment info is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $paymentMethod = new PosPayment();
        $paymentMethod->profile_id = Auth::user()->posProfile->id;
        $paymentMethod->payment_method = $request->method_name;
        $paymentMethod->payment_type = $request->payment_type;
        $filepath = '';
        if ($request->hasFile('payment_file')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-payment-method-image';
            $image = $request->file('payment_file');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'payment_file',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filepath = $responseData['path'];
        }
        $paymentMethod->payment_info = $request->payment_info;
        $paymentMethod->payment_file = $filepath ?? 'images/pos/payment/methods/default.png';
        $paymentMethod->save();

        return response()->json($paymentMethod, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentMethod = PosPayment::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (!$paymentMethod || $paymentMethod->count() < 1) {
            return response()->json([
                'message' => 'No payment method found'
            ], 404);
        }
        return response()->json($paymentMethod, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make([
            'method_name' => 'required|string',
            'payment_info' => 'required|string',
        ], [
            'method_name.required' => 'method name is required',
            'payment_info.required' => 'Payment info is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $paymentMethod = PosPayment::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (!$paymentMethod || $paymentMethod->count() < 1) {
            return response()->json([
                'message' => 'No payment method found'
            ], 404);
        }

        $paymentMethod->payment_method = $request->method_name;
        $paymentMethod->payment_info = $request->payment_info;
        $paymentMethod->save();

        return response()->json($paymentMethod, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentMethod = PosPayment::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();
        if (!$paymentMethod || $paymentMethod->count() < 1) {
            return response()->json([
                'message' => 'No payment method found'
            ], 404);
        }
        $paymentMethod->delete();
        return response()->json([
            'message' => 'Payment method deleted'
        ], 200);
    }
}
