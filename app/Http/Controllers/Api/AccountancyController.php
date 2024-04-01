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
use Illuminate\Support\Facades\Log;
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

        if ($request->start == $request->end) {
            $posAccountancy = PosAccountancy::with(['posSession'])
                ->where('profile_id', Auth::user()->posProfile->id)
                ->whereDate('created_at', $request->start)
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
    public function storeAllHistory(Request $request)
    {
        $orderDetailData = [];
        $profileId = Auth::user()->posProfile->id;

        if (!$request->input()) {
            return response()->json([
                'error' => "please fill data"
            ], 400);
        }

        return response()->json($request->input(), 200);

        foreach ($request->input() as $key => $inputData) {
            $posOrder = new PosOrder();
            $posOrder->profile_id = $profileId;
            $posOrder->session_id = $inputData['session_id'];
            $posOrder->order_code = $this->generateOrderCode($profileId);
            $posOrder->order_status = 'complete';
            $posOrder->total_item_qty = $inputData['total_item_qty'];
            $posOrder->order_subtotal = $inputData['order_subtotal'];
            $posOrder->extra_note = $inputData['extra_note'] ?? 'tidak ada';
            $posOrder->save();

            $posSale = new PosSale();
            $posSale->order_id = $posOrder->id;
            $posSale->payment_method_id = $inputData['payment_method_id'];
            $posSale->promo_id = $inputData['promo_id'];
            // $posSale->transaction_code = $this->generateTransactionCode($posOrder->id);
            $posSale->transaction_code = $inputData['transaction_code'];
            $posSale->payment_status = 'paid';
            $posSale->grand_total = $inputData['grand_total'];
            $posSale->paid_amount = ($posSale->grand_total - $inputData['change_amount']);
            $posSale->change_amount = $inputData['change_amount'];
            $posSale->paid_date = $inputData['paid_date'];
            $posSale->save();

            Log::info('Data transaksi ID: ' . $posSale->id);

            foreach ($inputData['detail_order'] as $key => $detailOrder) {
                $orderDetailData[] = [
                    'order_id' => $posSale->id,
                    'product_id' => $detailOrder['product_id'],
                    'item_quantity' => $detailOrder['item_quantity'],
                    'item_subtotal' => $detailOrder['item_subtotal']
                ];
                Log::info('Data Order Ke: ' . $key);
            }

            $posDetailOrder = PosDetailOrder::insert($orderDetailData);
        }

        $this->updateUserInventory($orderDetailData);

        return response()->json([
            'data' => $request->input(),
            'message' => 'berhasil tersimpan'
        ], 200);
    }
    public function generateOrderCode($profileId)
    {
        // $lastRecord = DB::table('pos_orders')
        //     ->where('profile_id', $profileId)
        //     ->latest('created_at')
        //     ->first();

        // if ($lastRecord) {
        //     $lastOrderNumber = $this->extractOrderNumber($lastRecord->order_code);
        //     $currentYear = now()->format('Y');
        //     $orderCode = $this->calculateNextOrderCode($lastOrderNumber, $lastRecord->created_at, $currentYear);
        // } else {
        //     $currentYear = now()->format('Y');
        //     $orderCode = 1;
        // }

        $orderCode = uniqid();
        $currentYear = now()->format('Y');
        $month = now()->format('m');

        return $orderCode . '/ORD/' . $month . '/' . $currentYear;
    }

    public function generateTransactionCode($orderId)
    {
        // $lastRecord = DB::table('pos_sales')
        //     ->where('order_id', $orderId)
        //     ->latest('created_at')
        //     ->first();

        // if ($lastRecord) {
        //     $lastOrderNumber = $this->extractOrderNumber($lastRecord->transaction_code);
        //     $currentYear = now()->format('Y');
        //     $TransactionCode = $this->calculateNextOrderCode($lastOrderNumber, $lastRecord->created_at, $currentYear);
        // } else {
        //     $currentYear = now()->format('Y');
        //     $TransactionCode = 1;
        // }

        $transactionCode = uniqid();
        $month = now()->format('m');
        $currentYear = now()->format('Y');

        return $transactionCode . '/TRANS/' . $month . '/' . $currentYear;
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
        $profileId = Auth::user()->posProfile->id;

        $postAccountancy = DB::table('pos_accountancies')
            ->join('pos_sessions', 'pos_sessions.id', 'pos_accountancies.session_id')
            ->select(
                'pos_accountancies.id as accountancy_id',
                'pos_accountancies.profile_id',
                'pos_accountancies.session_id',
                'pos_accountancies.accountancy_name',
                'pos_accountancies.transaction_type',
                'pos_accountancies.cash_transaction_amount',
                'pos_accountancies.digital_transaction_amount',
                'pos_accountancies.extra_note',
                'pos_accountancies.attachment_image',
                'pos_accountancies.created_at',
                'pos_sessions.employee_name',
                'pos_sessions.balance',
                'pos_sessions.opening_cash',
                'pos_sessions.closing_cash',
                'pos_sessions.session_start',
                'pos_sessions.session_end',
                'pos_sessions.session_notes',
            )
            ->where('pos_accountancies.profile_id', $profileId)
            ->where('pos_accountancies.id', $id)
            ->first();

        if (!$postAccountancy) {
            return response()->json(['error' => 'pembukuan tidak ditemukan'], 404);
        }

        $posDetailOrders = PosOrder::with(['posSale' => function ($query) {
            $query->with([
                'posPayment' => function ($query) {
                    $query->select('id', 'payment_method'); // Select only the columns you need from PosPayment
                },
                'posPromo' => function ($query) {
                    $query->select('id', 'promo_name'); // Select only the columns you need from Promo
                }
            ]);
        }])
            ->where('profile_id', $profileId)
            ->where('session_id', $postAccountancy->session_id)
            ->get();



        $totalSale = count($posDetailOrders);

        $totalItemSold = PosOrder::with('posSale')
            ->where('profile_id', $profileId)
            ->where('session_id', $postAccountancy->session_id)
            ->sum('total_item_qty');

        $grandTotalSum = PosOrder::with('posSale')
            ->where('profile_id', $profileId)
            ->where('session_id', $postAccountancy->session_id)
            ->get()
            ->sum(function ($order) {
                return optional($order->posSale)->grand_total ?? 0;
            });

        return response()->json([
            'grand_total_sum' => $grandTotalSum,
            'total_item_sold_inSession' => $totalItemSold,
            'total_transaction_inSession' => $totalSale,
            'pos_accountancy' => $postAccountancy,
            'pos_order' => $posDetailOrders
        ], 200);
    }

    public function showItems($id, $order_id)
    {
        $detailOrder = PosDetailOrder::with('posProduct')->where('order_id', $order_id)->get();

        if (!$detailOrder) {
            return response()->json(['error' => 'data not found'], 404);
        }

        return response()->json($detailOrder, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUserInventory($detailOrders)
    {
        $dataToUpdate = [];
        foreach ($detailOrders as $key => $detailOrder) {
            $dataToUpdate[] = [
                'product_id' => $detailOrder['product_id'],
                'quantity' => $detailOrder['item_quantity']
            ];
        }

        if (!empty($dataToUpdate)) {
            $this->updateInventoryData($dataToUpdate);
        }
    }

    public function updateInventoryData($dataToUpdate)
    {
        $productIds = collect($dataToUpdate)->pluck('product_id')->toArray();
        $quantities = collect($dataToUpdate)->pluck('quantity')->toArray();

        DB::table('pos_inventories')
            ->whereIn('product_id', $productIds)
            ->update(['quantity' => DB::raw('quantity - CASE ' . implode(' ', array_map(function ($productId, $quantity) {
                return "WHEN product_id = $productId THEN $quantity ";
            }, $productIds, $quantities)) . 'END')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //code
    }
}
