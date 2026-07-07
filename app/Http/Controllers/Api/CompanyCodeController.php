<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use SimpleSoftwareIO\QrCode\Facade\QrCode;

class CompanyCodeController
{
    /**
     * Return the client code, QR data URI and regeneration permission for a given company.
     */
    public function show(string $clientId): JsonResponse
    {
        $company = Company::find($clientId);
        if (! $company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        // Build QR code data URI if code exists
        $qrDataUri = null;
        if ($company->client_code) {
            // Simple QR with the code string; you can customize the payload as needed
            $svg = QrCode::format('svg')->size(200)->generate($company->client_code);
            $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($svg);
        }

        // Only company admins can regenerate the code
        $user = Auth::user();
        $canRegenerate = $user && $user->role === 'company_admin' && $user->client_id == $company->id;

        return response()->json([
            'client_code'   => $company->client_code,
            'qr_data_uri'   => $qrDataUri,
            'can_regenerate'=> $canRegenerate,
        ]);
    }
}
?>
