<?php

namespace App\Http\Controllers;

use App\Models\UserClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    /**
     * Retourne les informations de l'utilisateur connecté,
     * ses permissions formatées, ses entreprises, et les restrictions de champs.
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Charger les relations nécessaires
        if ($user->relationLoaded('roleModel')) {
            $user->load('roleModel');
        }

        // Récupérer les entreprises de l'utilisateur
        $companies = UserClient::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('client')
            ->get()
            ->map(function ($uc) {
                return $uc->client ? [
                    'id' => $uc->client->id,
                    'company_name' => $uc->client->company_name,
                    'rccm' => $uc->client->rccm,
                    'ifu' => $uc->client->ifu,
                ] : null;
            })
            ->filter()
            ->values();

        // Récupérer les permissions formatées
        $allPermissions = $user->getFormattedPermissions();

        // Récupérer les modules uniques
        $modules = array_values(array_unique(
            array_map(fn($p) => explode(':', $p)[0], $allPermissions)
        ));

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_id' => $user->role_id,
                'role_name' => $user->roleModel?->name,
                'role_slug' => $user->roleModel?->slug,
                'fonction' => $user->fonction,
                'phone' => $user->phone,
                'photo' => $user->photo,
                'client_id' => $user->client_id,
                'active_client_id' => $user->active_client_id,
                'is_active' => $user->is_active,
                'is_super_admin' => $user->isSuperAdmin(),
                'is_company_admin' => $user->isCompanyAdmin(),
                'is_comptable' => $user->isComptable(),
                'is_client' => $user->isClient(),
                'is_suspended' => $user->is_suspended ?? false,
                'must_change_password' => $user->must_change_password ?? false,
                'email_verified_at' => $user->email_verified_at,
                'role_secretaire' => $user->role_secretaire ?? false,
                'is_admin' => $user->is_admin ?? false,
            ],
            'permissions' => $allPermissions,
            'modules' => $modules,
            'companies' => $companies,
            'has_multiple_companies' => $companies->count() > 1,
            'active_company' => $companies->firstWhere('id', $user->active_client_id) ?? null,
        ]);
    }

    /**
     * Vérifie les mises à jour des permissions (utilisé pour le polling).
     */
    public function checkPermissions(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Version simple : retourner les permissions actuelles
        // Le frontend compare avec ce qu'il a en cache
        return response()->json([
            'permissions' => $user->getFormattedPermissions(),
            'modules' => array_values(array_unique(
                array_map(fn($p) => explode(':', $p)[0], $user->getFormattedPermissions())
            )),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Retourne les restrictions de champs pour un module donné.
     */
    public function fieldRestrictions(Request $request, string $module): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $hiddenFields = $user->hiddenFields($module);

        return response()->json([
            'module' => $module,
            'hidden_fields' => $hiddenFields,
        ]);
    }

    /**
     * Basculer le contexte d'entreprise.
     */
    public function switchContext(Request $request): JsonResponse
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
        ]);

        $user = Auth::user();
        $clientId = (int) $request->client_id;

        $success = $user->switchToClient($clientId);

        if (!$success) {
            return response()->json(['message' => 'Entreprise non accessible.'], 403);
        }

        return response()->json([
            'message' => 'Contexte basculé.',
            'active_client_id' => $user->fresh()->active_client_id,
        ]);
    }
}
