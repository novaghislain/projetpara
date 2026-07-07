<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanyCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CompanyCodeLookupController extends Controller
{
    /**
     * Valide un code entreprise et retourne les infos publiques.
     * Utilisé par le formulaire d'inscription pour vérification côté client.
     */
    public function lookup(Request $request): JsonResponse
    {
        $code = strtoupper(trim($request->input('code', '')));

        if (empty($code)) {
            return response()->json([
                'valid' => false,
                'message' => 'Veuillez saisir un code entreprise.',
            ]);
        }

        // Rate limiting : 10 lookups par minute par IP
        $rateKey = 'code-lookup:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 10)) {
            return response()->json([
                'valid' => false,
                'message' => 'Trop de tentatives. Réessayez dans une minute.',
            ], 429);
        }
        RateLimiter::hit($rateKey, 60);

        $codeService = app(CompanyCodeService::class);
        $client = $codeService->resolve($code);

        if (!$client) {
            return response()->json([
                'valid' => false,
                'message' => 'Code entreprise invalide. Vérifiez auprès de votre entreprise.',
            ]);
        }

        return response()->json([
            'valid'   => true,
            'company' => [
                'name'  => $client->company_name,
                'city'  => $client->city,
            ],
        ]);
    }
}
