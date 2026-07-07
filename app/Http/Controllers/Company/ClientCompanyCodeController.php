<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\UserClient;
use App\Services\CompanyCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class ClientCompanyCodeController extends Controller
{
    /**
     * Récupère les infos du code entreprise pour l'affichage.
     */
    public function show(int $clientId): JsonResponse
    {
        $this->authorizeAccess($clientId);

        $client = Client::findOrFail($clientId);
        $codeService = app(CompanyCodeService::class);

        $data = [
            'client_code' => $client->client_code,
            'qr_data_uri' => $client->client_code
                ? $codeService->qrCodeDataUri($client->client_code)
                : null,
        ];

        // Inclure la permission de régénération
        $user = Auth::user();
        $data['can_regenerate'] = $user->isSuperAdmin() || $user->isCompanyAdmin();

        return response()->json($data);
    }

    /**
     * Régénère le code entreprise (super_admin ou company_admin uniquement).
     */
    public function regenerate(int $clientId): JsonResponse
    {
        $this->authorizeAccess($clientId);

        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isCompanyAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Seul un super administrateur ou le responsable de l\'entreprise peut régénérer le code.',
            ], 403);
        }

        // Rate limiting : 3 régénérations par heure par client
        $rateKey = 'regenerate-code:' . $clientId;
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Trop de régénérations. Réessayez dans ' . RateLimiter::availableIn($rateKey) . ' secondes.',
            ], 429);
        }

        try {
            $oldCode = Client::where('id', $clientId)->value('client_code');
            $codeService = app(CompanyCodeService::class);
            $newCode = $codeService->generate();

            DB::transaction(function () use ($clientId, $newCode, $oldCode) {
                Client::where('id', $clientId)->update(['client_code' => $newCode]);
            });

            RateLimiter::hit($rateKey, 3600); // 1 heure de fenêtre

            return response()->json([
                'success'      => true,
                'client_code'  => $newCode,
                'qr_data_uri'  => $codeService->qrCodeDataUri($newCode),
                'message'      => 'Code régénéré avec succès. L\'ancien code (' . $oldCode . ') n\'est plus valide.',
            ]);

        } catch (\Exception $e) {
            logger()->error('Erreur régénération code entreprise', [
                'client_id' => $clientId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la régénération du code.',
            ], 500);
        }
    }

    /**
     * Vérifie que l'utilisateur a accès à ce client.
     */
    private function authorizeAccess(int $clientId): void
    {
        $user = Auth::user();
        if ($user->isSuperAdmin()) {
            return;
        }
        if ((int) $user->client_id === $clientId) {
            return;
        }
        abort(403, 'Accès non autorisé aux données de cette entreprise.');
    }

    /**
     * Ajoute une entreprise au compte de l'utilisateur connecté via un code.
     */
    public function addCompany(Request $request): JsonResponse
    {
        $request->validate([
            'client_code' => 'required|string|max:20',
        ]);

        $codeService = app(CompanyCodeService::class);
        $client = $codeService->resolve($request->client_code);

        if (!$client) {
            return response()->json([
                'success' => false,
                'errors' => ['client_code' => ['Code entreprise invalide.']],
            ], 422);
        }

        $user = Auth::user();

        if (!$codeService->canAttachUser($user->id, $client->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà rattaché à cette entreprise.',
            ], 422);
        }

        try {
            UserClient::create([
                'user_id'   => $user->id,
                'client_id' => $client->id,
                'role'      => 'client',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vous êtes maintenant rattaché à ' . $client->company_name . '.',
                'company' => [
                    'id'   => $client->id,
                    'name' => $client->company_name,
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('Erreur ajout entreprise', [
                'user_id' => $user->id,
                'client_id' => $client->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout.',
            ], 500);
        }
    }
}
