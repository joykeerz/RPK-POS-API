<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PrinterController extends Controller
{
    public function index()
    {
        $printers = PosPrinter::where('profile_id', Auth::user()->posProfile->id);
        if (empty($printers) || count($printers) == 0) {
            return response()->json([
                'error' => "there's no printer yet in this account"
            ], 404);
        }
        return response()->json($printers, 200);
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

        $printer = new PosPrinter();
        $printer->profile_id = Auth::user()->posProfile->id;
        $printer->printer_name = $request->printer_name;
        $printer->printer_address = $request->printer_address;
        $printer->save();

        if (!$printer) {
            return response()->json('printer failed to save', 200);
        }

        return response()->json($printer, 200);
    }
}
