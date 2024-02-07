<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosAccountancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
