<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosAccountancy;
use App\Models\PosDetailOrder;
use App\Models\PosOrder;
use App\Models\PosSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posAccountancy = PosAccountancy::with(['posSession'])
            ->where('profile_id', Auth::user()->posProfile->id)
            ->get();

        return response()->json($posAccountancy, 200);
    }

    public function getAccountancyToday()
    {
        $posAccountancy = PosAccountancy::with(['posSession'])
            ->where('profile_id', Auth::user()->posProfile->id)
            ->whereDate('created_at', now())
            ->get();

        return response()->json($posAccountancy, 200);
    }

    public function getAccountancyThisWeek()
    {
        $posAccountancy = PosAccountancy::with(['posSession'])
            ->where('profile_id', Auth::user()->posProfile->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get();

        return response()->json($posAccountancy, 200);
    }

    public function getAccountancyBetween(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start' => 'required|date',
            'end' => 'required|date',
        ], [
            'start.required' => 'start date harus di isi',
            'end.required' => 'end date harus di isi',
            'start.date' => 'start date harus berupa tanggal',
            'end.date' => 'end date harus berupa tanggal',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        if ($request->start  == $request->end) {
            $posAccountancy = PosAccountancy::with(['posSession'])
                ->where('profile_id', Auth::user()->posProfile->id)
                ->where('created_at', $request->start)
                ->get();
            return response()->json($posAccountancy, 200);
        }

        $posAccountancy = PosAccountancy::with(['posSession'])
            ->where('profile_id', Auth::user()->posProfile->id)
            ->whereBetween('created_at', [$request->start, $request->end])
            ->whereDate('created_at', '!=', $request->start)
            ->whereDate('created_at', '!=', $request->end)
            ->get();

        return response()->json($posAccountancy, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function storeAllHistory(Request $request)
    {
        $orderDetailData = [];
        $profileId = Auth::user()->posProfile->id;

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        foreach ($request->input() as $key => $inputData) {
            $orderCode = $this->generateOrderCode($profileId);

            $posOrder = new PosOrder();
            $posOrder->profile_id = $profileId;
            $posOrder->order_code = $orderCode;
            $posOrder->order_status = 'complete';
            $posOrder->total_item_qty = $inputData['total_item_qty'];
            $posOrder->order_subtotal = $inputData['order_subtotal'];
            $posOrder->extra_note = $inputData['extra_note'] ?? 'tidak ada';
            $posOrder->save();

            $posSale = new PosSale();
            $posSale->order_id = $posOrder->id;
            $posSale->payment_method_id = $inputData['payment_method_id'];
            $posSale->promo_id = $inputData['promo_id'];
            $posSale->transaction_code = $this->generateTransactionCode($posOrder->id);
            $posSale->payment_status = 'paid';
            $posSale->grand_total = $inputData['grand_total'];
            $posSale->paid_amount = $inputData['paid_amount'];
            $posSale->change_amount = $inputData['change_amount'];
            $posSale->paid_date = $inputData['paid_date'];
            $posSale->save();

            foreach ($inputData['detail_order'] as $key => $detailOrder) {
                $orderDetailData[] = [
                    'order_id' => $posSale->id,
                    'product_id' => $detailOrder['product_id'],
                    'item_quantity' => $detailOrder['item_quantity'],
                    'item_subtotal' => $detailOrder['item_subtotal']
                ];
            }

            $posDetailOrder = PosDetailOrder::insert($orderDetailData);
        }

        return response()->json([
            'data' => $request->input(),
            'message' => 'berhasil tersimpan'
        ], 200);
    }

    public function generateOrderCode($profileId)
    {
        $lastRecord = DB::table('pos_orders')
            ->where('profile_id', $profileId)
            ->latest('created_at')
            ->first();

        if ($lastRecord) {
            $lastOrderNumber = $this->extractOrderNumber($lastRecord->order_code);
            $currentYear = now()->format('Y');
            // $orderCode = $this->calculateNextOrderCode($lastOrderNumber, $lastRecord->created_at, $currentYear);
        } else {
            $currentYear = now()->format('Y');
            // $orderCode = 1;
        }

        $month = now()->format('m');

        return now()->format('His') . '/ORD/' . $month . '/' . $currentYear;
    }

    public function generateTransactionCode($orderId)
    {
        $lastRecord = DB::table('pos_sales')
            ->where('order_id', $orderId)
            ->latest('created_at')
            ->first();

        if ($lastRecord) {
            $lastOrderNumber = $this->extractOrderNumber($lastRecord->transaction_code);
            $currentYear = now()->format('Y');
            // $TransactionCode = $this->calculateNextOrderCode($lastOrderNumber, $lastRecord->created_at, $currentYear);
        } else {
            $currentYear = now()->format('Y');
            // $TransactionCode = 1;
        }

        $month = now()->format('m');

        return now()->format('His') . '/TRANS/' . $month . '/' . $currentYear;
    }

    private function extractOrderNumber($orderCode)
    {
        return (int)substr($orderCode, 0, 5);
    }

    private function calculateNextOrderCode($lastOrderNumber, $created_at, $currentYear)
    {
        $lastInsertYear = date('Y', strtotime($created_at));

        return $currentYear != $lastInsertYear ? 1 : $lastOrderNumber + 1;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $postAccountancy = PosAccountancy::with(['posSession'])
            ->where('profile_id', Auth::user()->posProfile->id)
            ->where('id', $id)
            ->first();

        if ($postAccountancy == null || !$postAccountancy) {
            return response()->json([
                'error' => 'Data not found'
            ], 404);
        }


        return response()->json($postAccountancy, 200);
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
