<?php

namespace App\Http\Controllers\Api\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur API pour la gestion des rôles et permissions.
 *
 * Permet de lister, créer et supprimer des rôles, gérer les permissions,
 * lister les utilisateurs et assigner des rôles aux utilisateurs.
 * Accessible uniquement aux administrateurs du portail GEL.
 */
class RoleApiController extends Controller
{
    /**
     * Constructeur : applique les middlewares d'authentification et de locataire (tenant).
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('tenant');
    }

    /**
     * Liste tous les rôles disponibles dans le portail de l'utilisateur.
     * Les super-admins voient tous les rôles ; les autres voient ceux de leur portail.
     *
     * @param Request $request La requête HTTP.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $roles = Role::withCount('users')->get();
        } else {
            // Déterminer le portail depuis l'utilisateur
            $portail = 'gel';
            if ($user->client_id) {
                $portail = 'entreprise';
            }

            $roles = Role::where('portail', $portail)
                ->orWhere('name', 'super_admin')
                ->withCount('users')
                ->get();
        }

        return response()->json($roles);
    }

    /**
     * Liste toutes les permissions disponibles dans le système.
     *
     * @param Request $request La requête HTTP.
     * @return \Illuminate\Http\JsonResponse
     */
    public function permissions(Request $request)
    {
        $permissions = Permission::all(['id', 'name', 'module', 'label_fr', 'description', 'portail']);
        return response()->json($permissions);
    }

    /**
     * Récupère les permissions associées à un rôle spécifique.
     *
     * @param Request $request La requête HTTP.
     * @param Role $role Le rôle dont on veut les permissions.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRolePermissions(Request $request, Role $role)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasPermissionTo('admin.acces')) {
            return response()->json(['message' => config('permission.messages.unauthorized')], 403);
        }

        $permissions = $role->permissions()->get(['id', 'name', 'module', 'label_fr', 'description']);
        return response()->json($permissions);
    }

    /**
     * Met à jour les permissions d'un rôle existant.
     * Empêche la modification du rôle super_admin par un non super-admin.
     *
     * @param Request $request La requête HTTP avec la liste des permissions.
     * @param Role $role Le rôle à modifier.
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasPermissionTo('admin.acces')) {
            return response()->json(['message' => config('permission.messages.unauthorized')], 403);
        }

        // Empêcher la modification du super_admin par un non super_admin
        if ($role->name === 'super_admin' && ! $user->isSuperAdmin()) {
            return response()->json(['message' => 'Seul un Super Administrateur peut modifier ce rôle'], 403);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:gel_permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $newPermissions = $request->input('permissions');

        $role->syncPermissions($newPermissions);

        // Audit log
        AuditTrail::create([
            'user_id'        => $user->id,
            'event'          => 'permissions_updated',
            'auditable_type' => Role::class,
            'auditable_id'   => $role->id,
            'old_values'     => ['permissions' => $oldPermissions],
            'new_values'     => ['permissions' => $newPermissions],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'description'    => 'Mise à jour des permissions du rôle : ' . $role->name,
        ]);

        return response()->json([
            'message' => 'Permissions mises à jour avec succès.',
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Crée un nouveau rôle personnalisé avec ses permissions.
     *
     * @param Request $request La requête HTTP avec les données du rôle.
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasPermissionTo('admin.acces')) {
            return response()->json(['message' => config('permission.messages.unauthorized')], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:gel_roles,name|regex:/^[a-z_]+$/',
            'label_fr' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'portail' => 'required|in:gel,entreprise,cpa',
            'level' => 'required|integer|min:0|max:9',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:gel_permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'label_fr' => $request->label_fr,
            'description' => $request->description,
            'portail' => $request->portail,
            'level' => $request->level,
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        AuditTrail::create([
            'user_id'        => $user->id,
            'event'          => 'role_created',
            'auditable_type' => Role::class,
            'auditable_id'   => $role->id,
            'new_values'     => $role->toArray(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'description'    => 'Création du rôle : ' . $role->name,
        ]);

        return response()->json([
            'message' => 'Rôle créé avec succès',
            'role' => $role->load('permissions'),
        ], 201);
    }

    /**
     * Supprime un rôle personnalisé (les rôles système sont protégés).
     *
     * @param Request $request La requête HTTP.
     * @param Role $role Le rôle à supprimer.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Role $role)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin()) {
            return response()->json(['message' => 'Seul un Super Administrateur peut supprimer un rôle'], 403);
        }

        // Empêcher la suppression des rôles système prédéfinis
        $systemRoles = ['super_admin', 'gestionnaire_cabinet', 'comptable_senior', 'chef_comptable', 'comptable_junior', 'auditeur'];
        if (in_array($role->name, $systemRoles)) {
            return response()->json(['message' => 'Ce rôle système ne peut pas être supprimé'], 422);
        }

        AuditTrail::create([
            'user_id'        => $user->id,
            'event'          => 'role_deleted',
            'auditable_type' => Role::class,
            'auditable_id'   => $role->id,
            'old_values'     => $role->toArray(),
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'description'    => 'Suppression du rôle : ' . $role->name,
        ]);

        $role->delete();

        return response()->json(['message' => 'Rôle supprimé avec succès']);
    }

    /**
     * Liste les utilisateurs avec leurs rôles Spatie assignés.
     *
     * @param Request $request La requête HTTP.
     * @return \Illuminate\Http\JsonResponse
     */
    public function users(Request $request)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasPermissionTo('admin.acces')) {
            return response()->json(['message' => config('permission.messages.unauthorized')], 403);
        }

        $users = User::with('roles')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_active' => $u->is_active,
                    'roles' => $u->roles->pluck('name'),
                    'last_login' => $u->last_login_at,
                ];
            });

        return response()->json($users);
    }

    /**
     * Assigne des rôles à un utilisateur cible.
     * Vérifie l'appartenance au même cabinet pour les non super-admins.
     *
     * @param Request $request La requête HTTP avec la liste des rôles.
     * @param User $targetUser L'utilisateur cible.
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoles(Request $request, User $targetUser)
    {
        $user = $request->user();

        if (! $user->isSuperAdmin() && ! $user->hasPermissionTo('admin.acces')) {
            return response()->json(['message' => config('permission.messages.unauthorized')], 403);
        }

        // Vérifier que l'utilisateur cible appartient au même cabinet
        if (! $user->isSuperAdmin()) {
            $userCabinetId = $user->cabinet_id ?? session('current_cabinet_id');
            $targetCabinetId = $targetUser->cabinet_id;
            if ($targetCabinetId && $userCabinetId && (int) $targetCabinetId !== (int) $userCabinetId) {
                return response()->json(['message' => 'Utilisateur non trouvé dans ce cabinet'], 404);
            }
        }

        $validator = Validator::make($request->all(), [
            'roles' => 'required|array',
            'roles.*' => 'string|exists:gel_roles,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldRoles = $targetUser->getRoleNames()->toArray();
        $targetUser->syncRoles($request->roles);

        AuditTrail::create([
            'user_id'        => $user->id,
            'event'          => 'user_roles_updated',
            'auditable_type' => User::class,
            'auditable_id'   => $targetUser->id,
            'old_values'     => ['roles' => $oldRoles],
            'new_values'     => ['roles' => $request->roles],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'description'    => 'Mise à jour des rôles de : ' . $targetUser->name,
        ]);

        return response()->json([
            'message' => 'Rôles mis à jour avec succès',
            'user' => [
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'roles' => $targetUser->getRoleNames(),
            ],
        ]);
    }
}
