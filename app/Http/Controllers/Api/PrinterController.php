<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrinterController extends Controller
{
    public function index()
    {
        $printers = PosPrinter::where('profile_id', Auth::user()->posProfile->id)->get();

        if (!$printers) {
            return response()->json("there's no printer yet in this account", 404);
        }
        return response()->json($printers, 200);
    }

    public function show(string $id)
    {
        $printer = PosPrinter::where('id', $id)->first();
        if (!$printer) {
            return response()->json([
                'error' => "printer not found"
            ], 404);
        }

        return response()->json($printer, 200);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'printer_name' => 'required',
            'printer_address' => 'required'
        ], [
            'printer_name.required' => 'printer name cannot be empty',
            'printer_address.required' => 'printer address cannot be empty'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        if (PosPrinter::where('profile_id', Auth::user()->posProfile->id)->where('printer_name', $request->printer_name)->first()) {
            return response()->json([
                'error' => "printer already exist"
            ], 500);
        }

        $printer = new PosPrinter();
        $printer->profile_id = Auth::user()->posProfile->id;
        $printer->printer_name = $request->printer_name;
        $printer->printer_address = $request->printer_address;
        $printer->save();

        if (!$printer) {
            return response()->json('printer failed to save', 500);
        }

        return response()->json($printer, 200);
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'printer_name' => 'required',
        ], [
            'printer_name.required' => 'printer name cannot be empty',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $printer = PosPrinter::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();

        if (!$printer) {
            return response()->json([
                'error' => "printer not found"
            ], 404);
        }

        $printer->printer_name = $request->printer_name;
        if ($request->printer_address) {
            $printer->printer_address = $request->printer_address;
        }
        $printer->save();

        return response()->json($printer, 200);
    }

    public function delete(string $id)
    {
        $printer = PosPrinter::where('profile_id', Auth::user()->posProfile->id)->where('id', $id)->first();

        if (!$printer) {
            return response()->json([
                'error' => "printer not found"
            ], 404);
        }

        $printer->delete();

        return response()->json([
            'message' => "printer deleted"
        ], 200);
    }
}
