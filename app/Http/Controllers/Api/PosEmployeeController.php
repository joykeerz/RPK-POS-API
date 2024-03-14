<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosEmployee;
use App\Models\PosProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PosEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posProfile = PosProfile::where('user_id', Auth::user()->id)->first();
        $posEmployee = PosEmployee::where('profile_id', $posProfile->id)->get();

        if (!$posEmployee) {
            return response()->json('no employee in this account', 200);
        }

        return response()->json($posEmployee, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $posProfile = PosProfile::where('user_id', Auth::user()->id)->first();

        if (!$request->input()) {
            return response()->json([
                'error' => 'no request data provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|min:6|max:6',
            'employee_name' => 'required',
            'employee_phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $posEmployee = new PosEmployee();
        $posEmployee->profile_id = $posProfile->id;
        $posEmployee->pin = $request->pin;
        $posEmployee->employee_name = $request->employee_name;
        $posEmployee->employee_phone = $request->employee_phone;
        $posEmployee->save();

        return response()->json($posEmployee, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $posEmployee = PosEmployee::where('id', $id)->first();
        if (!$posEmployee) {
            return response()->json([
                'error' => 'employee not found'
            ]);
        }

        return response()->json($posEmployee, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'no request data provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'string|min:6|max:6',
            'employee_name' => 'required',
            'employee_phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $posEmployee = PosEmployee::where('id', $id)->first();
        return $posEmployee;
        if (!$posEmployee) {
            return response()->json('no employee in this account', 200);
        }
        if ($posEmployee->pin) {
            $posEmployee->pin = $request->pin;
        }else{
            $posEmployee->pin = $posEmployee->pin;
        }
        $posEmployee->employee_name = $request->employee_name;
        $posEmployee->employee_phone = $request->employee_phone;
        $posEmployee->save();

        return response()->json($posEmployee, 200);
    }

    public function updateEmployeePin(Request $request, string $id)
    {
        if (!$request->input()) {
            return response()->json([
                'error' => 'no pin provided'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'pin' => 'required|string|min:6|max:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 400);
        }

        $posEmployee = PosEmployee::where('id', $id)->first();
        if (!$posEmployee) {
            return response()->json('no employee in this account', 200);
        }

        $posEmployee->pin = $request->pin;
        $posEmployee->save();

        return response()->json(['pin' => $posEmployee->pin], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $posEmployee = PosEmployee::where('id', $id)->delete();
        if (!$posEmployee) {
            return response()->json('no employee in this account', 200);
        }

        return response()->json([
            'message' => "Employee successfully deleted"
        ], 200);
    }
}
