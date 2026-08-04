<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserClient;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Contrôleur de gestion des administrateurs entreprise et des rôles/permissions.
 * Permet de gérer les utilisateurs, leurs rôles, les permissions,
 * ainsi que la création/suppression des administrateurs entreprise.
 * Utilise Spatie Permission pour la gestion des rôles et permissions.
 */
class CompanyAdminController extends Controller
{
    /**
     * Affiche la page de gestion des administrateurs entreprise.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('app', ['page' => 'gel-company-admins']);
    }

    /**
     * API : Retourne tous les utilisateurs administrateurs entreprise.
     * Inclut ceux avec is_company_admin=true OU client_id non nul,
     * avec leur relation client.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAll()
    {
        $admins = User::where(function ($query) {
                $query->where('is_company_admin', true)
                      ->orWhereNotNull('client_id');
            })
            ->with('client')
            ->latest()
            ->get();

        return response()->json($admins);
    }

    /**
     * API : Retourne un administrateur entreprise spécifique.
     *
     * @param int $id L'identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::where(function ($query) {
                $query->where('is_company_admin', true)
                      ->orWhereNotNull('client_id');
            })
            ->with('client')
            ->findOrFail($id);

        return response()->json($user);
    }

    /**
     * API : Retourne tous les utilisateurs du cabinet avec leurs rôles et permissions.
     * Paginé par 50 utilisateurs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listUsers()
    {
        $users = User::with(['roles', 'permissions'])
            ->latest()
            ->paginate(50)
            ->through(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_active' => $u->is_active,
                'last_login' => $u->last_login_at,
                'roles' => $u->roles->pluck('label_fr', 'name')->map(fn($l, $n) => $l ?: $n)->values(),
                'permissions' => $u->getAllPermissions()->pluck('name'),
            ]);

        return response()->json($users->items());
    }

    /**
     * API : Met à jour les rôles d'un utilisateur.
     * Remplace tous les rôles existants par ceux fournis.
     *
     * @param Request $request La requête HTTP avec la liste des rôles
     * @param int $id L'identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string|exists:gel_roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles($request->roles);

        return response()->json([
            'success' => true,
            'message' => 'Rôles mis à jour avec succès.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }

    /**
     * API : Retourne la liste des rôles disponibles avec le nombre d'utilisateurs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listRoles()
    {
        $roles = Role::withCount('users')
            ->orderBy('level')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'label_fr' => $r->label_fr ?? $r->name,
                'description' => $r->description,
                'portail' => $r->portail ?? 'gel',
                'level' => $r->level,
                'users_count' => $r->users_count,
            ]);

        return response()->json($roles);
    }

    /**
     * API : Retourne la liste des permissions disponibles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listPermissions()
    {
        $permissions = Permission::orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'label_fr' => $p->label_fr ?? $p->name,
                'module' => $p->module ?? 'general',
                'description' => $p->description,
                'portail' => $p->portail ?? 'gel',
            ]);

        return response()->json($permissions);
    }

    /**
     * API : Crée un nouveau rôle avec ses permissions associées.
     *
     * @param Request $request La requête HTTP avec les données du rôle
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:gel_roles,name',
            'label_fr' => 'required|string|max:255',
            'description' => 'nullable|string',
            'portail' => 'nullable|string|in:gel,entreprise,cpa',
            'level' => 'nullable|integer|min:0|max:5',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:gel_permissions,name',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'label_fr' => $validated['label_fr'],
            'description' => $validated['description'] ?? null,
            'portail' => $validated['portail'] ?? 'gel',
            'level' => $validated['level'] ?? 3,
            'guard_name' => 'web',
        ]);

        // Associer les permissions au rôle si fournies
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'role' => $role,
            'message' => 'Rôle créé avec succès.',
        ], 201);
    }

    /**
     * API : Met à jour les permissions et les métadonnées d'un rôle.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param string $name Le nom du rôle à modifier
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRolePermissions(Request $request, $name)
    {
        $role = Role::findByName($name);

        $validated = $request->validate([
            'label_fr' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'portail' => 'sometimes|string|in:gel,entreprise,cpa',
            'level' => 'sometimes|integer|min:0|max:5',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:gel_permissions,name',
        ]);

        $role->update([
            'label_fr' => $validated['label_fr'] ?? $role->label_fr,
            'description' => $validated['description'] ?? $role->description,
            'portail' => $validated['portail'] ?? $role->portail,
            'level' => $validated['level'] ?? $role->level,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'success' => true,
            'role' => $role->fresh(),
            'message' => 'Rôle mis à jour avec succès.',
        ]);
    }

    /**
     * API : Supprime un rôle.
     * Les rôles système (super_admin, gestionnaire_cabinet) sont protégés
     * et ne peuvent pas être supprimés.
     *
     * @param string $name Le nom du rôle à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyRole($name)
    {
        // Empêcher la suppression des rôles système critiques
        $protectedRoles = ['super_admin', 'gestionnaire_cabinet'];
        if (in_array($name, $protectedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce rôle système ne peut pas être supprimé.',
            ], 403);
        }

        $role = Role::findByName($name);
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rôle supprimé avec succès.',
        ]);
    }

    /**
     * API : Crée un nouvel administrateur entreprise.
     * Crée l'utilisateur et l'associe au client via la table pivot user_clients
     * pour que le middleware ensure.company autorise l'accès au dashboard.
     *
     * @param Request $request La requête HTTP avec name, email, password, client_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:8',
            'client_id'  => 'required|exists:clients,id',
            'role'       => 'sometimes|in:company_admin',
        ]);

        $user = User::create([
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'client_id'        => $validated['client_id'],
            'is_company_admin' => true,
            'role'             => 'company_admin',
            'is_active'        => true,
        ]);

        // Associer l'admin à l'entreprise dans user_clients
        // Obligatoire pour que le middleware ensure.company autorise l'accès au dashboard
        UserClient::firstOrCreate(
            [
                'user_id'   => $user->id,
                'client_id' => $validated['client_id'],
            ],
            [
                'role'      => 'company_admin',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );

        return response()->json($user->load('client'), 201);
    }

    /**
     * API : Met à jour un administrateur entreprise.
     * Ne change le mot de passe que si le champ est fourni et non vide.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => 'sometimes|nullable|string|min:8',
            'client_id' => 'sometimes|exists:clients,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = collect($validated)->except('password')->toArray();

        // Ne changer le mot de passe que s'il est fourni et non vide
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return response()->json($user->load('client'));
    }

    /**
     * API : Supprime un administrateur entreprise.
     * Vérifie que l'utilisateur est bien un company_admin avant suppression.
     *
     * @param int $id L'identifiant de l'utilisateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (!$user->is_company_admin) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas un administrateur entreprise.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Administrateur entreprise supprimé avec succès.'], 200);
    }
}
