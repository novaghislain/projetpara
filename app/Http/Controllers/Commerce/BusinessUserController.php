<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use App\Models\BusinessRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessUserController extends Controller
{
    /**
     * Contrôleur pour la gestion des utilisateurs commerciaux.
     * Permet d'associer des utilisateurs à une entreprise avec des rôles
     * et permissions spécifiques (admin_entreprise, dg, commercial, etc.).
     */

    /**
     * Affiche la page de gestion des utilisateurs commerciaux.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'commerce-business-users']);
    }

    /**
     * Retourne la liste de tous les utilisateurs commerciaux de l'entreprise.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll()
    {
        $user = Auth::user();
        $clientId = $user->client_id ?? $user->id;

        $users = BusinessUser::with(['user', 'role'])
            ->where('client_id', $clientId)
            ->latest()
            ->get();

        return response()->json($users);
    }

    /**
     * Associe un utilisateur existant à l'entreprise avec un rôle spécifique.
     * Vérifie l'unicité de l'association.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'nullable|exists:business_roles,id',
            'business_role' => 'required|in:admin_entreprise,dg,commercial,stock_manager,cashier,auditor',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        // Vérifier l'unicité
        $existing = BusinessUser::where('client_id', $clientId)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Cet utilisateur est déjà lié à cette entreprise'], 422);
        }

        $validated['client_id'] = $clientId;
        $validated['created_by'] = Auth::id();

        $businessUser = BusinessUser::create($validated);

        return response()->json($businessUser->load(['user', 'role']), 201);
    }

    /**
     * Met à jour le rôle ou les permissions d'un utilisateur commercial.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id Identifiant de l'utilisateur commercial
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $businessUser = BusinessUser::findOrFail($id);

        $validated = $request->validate([
            'role_id' => 'nullable|exists:business_roles,id',
            'business_role' => 'sometimes|in:admin_entreprise,dg,commercial,stock_manager,cashier,auditor',
            'is_active' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        $businessUser->update($validated);
        return response()->json($businessUser->load(['user', 'role']));
    }

    /**
     * Supprime l'association d'un utilisateur à l'entreprise.
     *
     * @param int $id Identifiant de l'utilisateur commercial
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $businessUser = BusinessUser::findOrFail($id);
        $businessUser->delete();
        return response()->json(['message' => 'Utilisateur commercial retiré']);
    }

    /**
     * Retourne la liste des utilisateurs disponibles pour être associés
     * (ceux qui ne sont pas déjà liés à l'entreprise).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableUsers()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();

        $existingIds = BusinessUser::where('client_id', $clientId)
            ->pluck('user_id');

        $users = User::whereNotIn('id', $existingIds)
            ->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhere('role', 'super_admin');
            })
            ->get(['id', 'name', 'email', 'role']);

        return response()->json($users);
    }

    /**
     * Retourne la liste des rôles personnalisés de l'entreprise.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();
        $roles = BusinessRole::where('client_id', $clientId)->get();
        return response()->json($roles);
    }

    /**
     * Crée un nouveau rôle personnalisé pour l'entreprise.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRole(Request $request)
    {
        $clientId = Auth::user()->client_id ?? Auth::id();

        $validated = $request->validate([
            'role' => 'required|string|max:50',
            'permissions' => 'nullable|array',
        ]);

        $validated['client_id'] = $clientId;
        $role = BusinessRole::create($validated);

        return response()->json($role, 201);
    }
}
