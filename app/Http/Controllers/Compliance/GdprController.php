<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Compliance\GdprConsent;

class GdprController extends Controller
{
    public function updateConsent(Request $request)
    {
        $request->validate([
            'consent_type' => 'required|in:terms,marketing,data_processing',
            'is_granted' => 'required|boolean'
        ]);

        $user = $request->user();

        $consent = GdprConsent::updateOrCreate(
            ['user_id' => $user->id, 'consent_type' => $request->consent_type],
            [
                'is_granted' => $request->is_granted,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'granted_at' => $request->is_granted ? now() : null,
                'revoked_at' => !$request->is_granted ? now() : null
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $consent,
            'message' => 'Consentement mis à jour.'
        ]);
    }

    public function getConsents(Request $request)
    {
        $consents = GdprConsent::where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $consents
        ]);
    }
}
