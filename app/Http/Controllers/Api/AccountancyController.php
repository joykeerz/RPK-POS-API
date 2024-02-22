<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosAccountancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // return now();
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
