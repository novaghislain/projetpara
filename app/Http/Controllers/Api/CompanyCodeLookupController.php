<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanyCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Contrôleur API pour la validation des codes entreprises.
 *
 * Utilisé par le formulaire d'inscription pour vérifier
 * l'existence d'une entreprise via son code client.
 * Inclut une limitation de débit (rate limiting) par IP.
 */
class CompanyCodeLookupController extends Controller
{
    /**
     * Valide un code entreprise et retourne les informations publiques de l'entreprise.
     * Limité à 10 requêtes par minute par adresse IP.
     *
     * @param Request $request La requête HTTP contenant le code entreprise.
     * @return JsonResponse
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
