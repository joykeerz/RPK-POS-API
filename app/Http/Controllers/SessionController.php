<?php

namespace App\Http\Controllers;

use App\Models\PosAccountancy;
use App\Models\PosEmployee;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    public function openSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_name' => 'required',
            'opening_cash' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $posSession = new PosSession();
        $posSession->profile_id = Auth::user()->posProfile->id;
        $posSession->employee_name = $request->employee_name;
        $posSession->balance = $request->opening_cash;
        $posSession->opening_cash = $request->opening_cash;
        $posSession->closing_cash = 0;
        $posSession->session_start = now();
        $posSession->session_end = now();
        $posSession->session_notes = $request->session_notes ?? '';
        $posSession->save();

        if (!$posSession) {
            return response()->json([
                'message' => 'Failed to open session',
            ], 500);
        }

        return response()->json([
            'message' => 'Session opened',
            'data' => $posSession,
        ], 200);
    }

    public function closeSession(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'closing_cash' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $posSession = PosSession::find($id);
        $posSession->closing_cash = $request->closing_cash;
        $posSession->balance = $request->closing_cash;
        $posSession->session_end = now();
        $posSession->session_notes = $request->session_notes ?? '';
        $posSession->save();

        $posAccountancy = new PosAccountancy();
        $posAccountancy->profile_id = Auth::user()->posProfile->id;
        $posAccountancy->session_id = $posSession->id;
        $posAccountancy->accountancy_name = 'Closing Cash' . ' ' . $posSession->employee_name;
        $posAccountancy->transaction_type = 'pemasukan';
        $posAccountancy->transaction_amount = $request->closing_cash;
        $posAccountancy->extra_note = $request->extra_notes ?? '';
        if ($request->hasFile('attachment_image')) {
            $url = env('API_DASHBOARD_URL') . '/mobile/receive-pembukuan-image';
            $image = $request->file('attachment_image');
            $fileName = 'image_' . time() . '.' . $image->getClientOriginalExtension();
            $imageContent = file_get_contents($image->getRealPath());
            $response = Http::attach(
                'attachment_image',
                $imageContent,
                $fileName
            )->post($url);
            $responseData = $response->json();
            $filePath = $responseData['path'];
        }
        $posAccountancy->attachment_image = $filePath ?? 'images/pos/accountancy/default.png';
        $posAccountancy->save();

        if (!$posSession || !$posAccountancy) {
            return response()->json([
                'message' => 'Failed to close session',
            ], 500);
        }

        return response()->json([
            'message' => 'Session closed',
            'data' => $posSession,
        ], 200);
    }

    public function getOpenSession()
    {
        $profileId = Auth::user()->posProfile->id;

        $sessions = PosSession::where('profile_id', $profileId)
            ->whereColumn('session_start', '=', 'session_end')
            ->get();

        return response()->json($sessions, 200);
    }

    public function getclosedSession()
    {
        $profileId = Auth::user()->posProfile->id;

        $sessions = PosSession::where('profile_id', $profileId)
            ->whereColumn('session_start', '!=', 'session_end')
            ->get();

        return response()->json($sessions, 200);
    }
}
