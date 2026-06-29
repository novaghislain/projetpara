<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends BaseCompanyController
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Seul l\'administrateur de l\'entreprise peut gérer les utilisateurs.');
        }
    }

    /**
     * Affiche la page de gestion des utilisateurs.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }
        return view('company', [
            'page' => 'company-users',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * API: Liste tous les utilisateurs de l'entreprise avec leurs permissions.
     */
    public function listAll()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $users = User::where('client_id', $clientId)
            ->with(['roleModel', 'directPermissionModels'])
            ->latest()
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'fonction' => $u->fonction,
                    'role_id' => $u->role_id,
                    'role_name' => $u->roleModel?->name ?? 'N/A',
                    'role_slug' => $u->roleModel?->slug ?? '',
                    'is_active' => $u->is_active,
                    'is_company_admin' => $u->is_company_admin,
                    'permissions' => $u->effectivePermissions()->pluck('id')->toArray(),
                    'modules' => $u->getAccessibleModules(),
                    'formatted_permissions' => $u->getFormattedPermissions(),
                    'created_at' => $u->created_at?->format('d/m/Y'),
                    'last_login' => $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Jamais',
                ];
            });

        // Rôles disponibles pour l'entreprise
        $availableRoles = Role::whereNotIn('slug', ['super_admin', 'company_admin'])
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'users' => $users,
            'roles' => $availableRoles,
        ]);
    }

    /**
     * Retourne un utilisateur spécifique avec ses permissions.
     */
    public function show($id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();
        $target = User::where('client_id', $clientId)
            ->with(['roleModel', 'directPermissionModels'])
            ->findOrFail($id);

        return response()->json([
            'id' => $target->id,
            'name' => $target->name,
            'email' => $target->email,
            'fonction' => $target->fonction,
            'role_id' => $target->role_id,
            'role_name' => $target->roleModel?->name ?? 'N/A',
            'is_active' => $target->is_active,
            'permissions' => $target->effectivePermissions()->pluck('id')->toArray(),
            'modules' => $target->getAccessibleModules(),
        ]);
    }

    /**
     * Crée un nouvel utilisateur dans l'entreprise.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();
        $currentUser = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'fonction' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Vérifier que le rôle n'est pas interdit
        if ($validated['role_id'] ?? null) {
            $role = Role::findOrFail($validated['role_id']);
            if (in_array($role->slug, ['super_admin', 'company_admin'])) {
                return response()->json(['message' => 'Ce rôle ne peut pas être attribué.'], 403);
            }
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_id' => $clientId,
            'role_id' => $validated['role_id'] ?? null,
            'role' => $validated['role_id'] ? Role::find($validated['role_id'])->slug : 'collaborator',
            'fonction' => $validated['fonction'] ?? null,
            'is_active' => true,
        ]);

        // Attribuer les permissions directes
        if (!empty($validated['permissions'])) {
            $this->syncUserPermissions($newUser->id, $validated['permissions'], $currentUser->id);
        }

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => [
                'id' => $newUser->id,
                'name' => $newUser->name,
                'email' => $newUser->email,
                'role_name' => $newUser->roleModel?->name,
                'fonction' => $newUser->fonction,
                'permissions' => $newUser->getDirectPermissionIds(),
            ],
        ], 201);
    }

    /**
     * Met à jour un utilisateur de l'entreprise.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();
        $currentUser = Auth::user();
        $target = User::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|nullable|exists:roles,id',
            'fonction' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'permissions' => 'sometimes|nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data = collect($validated)->except('password', 'permissions')->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Si le rôle change, mettre à jour le champ `role` (string)
        if (isset($validated['role_id'])) {
            if ($validated['role_id']) {
                $role = Role::find($validated['role_id']);
                if ($role && in_array($role->slug, ['super_admin', 'company_admin'])) {
                    return response()->json(['message' => 'Ce rôle ne peut pas être attribué.'], 403);
                }
                if ($role) {
                    $data['role'] = $role->slug;
                }
            } else {
                $data['role'] = 'collaborator';
            }
        }

        $target->update($data);

        // Mettre à jour les permissions directes si fournies
        if (isset($validated['permissions'])) {
            $this->syncUserPermissions($target->id, $validated['permissions'], $currentUser->id);
        }

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'user' => $target->fresh()->load(['roleModel', 'directPermissionModels']),
        ]);
    }

    /**
     * Supprime un utilisateur.
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();
        $user = Auth::user();
        $target = User::where('client_id', $clientId)->findOrFail($id);

        if ((int) $target->id === (int) $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        // Supprimer les permissions directes
        UserPermission::where('user_id', $target->id)->delete();
        $target->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }

    // ─── Permissions ─────────────────────────────────────────────────

    /**
     * API: Met à jour les permissions d'un utilisateur spécifique.
     */
    public function updatePermissions(Request $request, $id)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();
        $currentUser = Auth::user();
        $target = User::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->syncUserPermissions($target->id, $validated['permissions'], $currentUser->id);

        return response()->json([
            'message' => 'Permissions mises à jour.',
            'permissions' => $target->fresh()->getDirectPermissionIds(),
            'modules' => $target->fresh()->getAccessibleModules(),
        ]);
    }

    /**
     * API: Retourne toutes les permissions disponibles, groupées par module.
     */
    public function availablePermissions()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        // Récupérer les modules sous licence de l'entreprise
        $licensedModules = \App\Models\License::where('client_id', $clientId)
            ->where('status', 'active')
            ->with('service')
            ->get()
            ->pluck('service.slug')
            ->toArray();

        // Toutes les permissions existantes
        $allPermissions = Permission::all();

        // Modules accessibles par l'admin entreprise (tous, sauf désactivés)
        $user = Auth::user();
        $adminModules = $user->getAccessibleModules();

        // Fusion : licences actives + modules admin + ged (toujours)
        $allowedModules = array_unique(array_merge(
            $licensedModules,
            $adminModules,
            ['document'] // ged toujours accessible
        ));

        $permissions = $allPermissions
            ->whereIn('module', $allowedModules)
            ->values();

        // Grouper par module
        $grouped = $permissions->groupBy('module')->map(function ($perms, $module) {
            return [
                'module' => $module,
                'label' => $this->moduleLabel($module),
                'icon' => $this->moduleIcon($module),
                'permissions' => $perms->map(fn($p) => [
                    'id' => $p->id,
                    'action' => $p->action,
                    'display_name' => $p->display_name,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'modules' => $grouped,
            'all_permissions' => $permissions,
        ]);
    }

    /**
     * API: Retourne les permissions de l'utilisateur connecté.
     */
    public function myPermissions()
    {
        $user = Auth::user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_company_admin' => $user->isCompanyAdmin(),
                'is_super_admin' => $user->isSuperAdmin(),
            ],
            'permissions' => $user->getFormattedPermissions(),
            'modules' => $user->getAccessibleModules(),
            'permission_ids' => $user->getDirectPermissionIds(),
        ]);
    }

    // ─── Privé ──────────────────────────────────────────────────────

    /**
     * Synchronise les permissions directes d'un utilisateur.
     */
    private function syncUserPermissions(int $userId, array $permissionIds, int $grantedBy): void
    {
        DB::transaction(function () use ($userId, $permissionIds, $grantedBy) {
            // Supprimer les anciennes permissions directes
            UserPermission::where('user_id', $userId)->delete();

            $user = User::find($userId);
            $rolePermissionIds = $user && $user->roleModel
                ? $user->roleModel->permissions()->pluck('permissions.id')->toArray()
                : [];

            // Comparer les tableaux (les deux triés et uniques pour être sûr)
            $pIds = array_values(array_unique($permissionIds));
            sort($pIds);
            sort($rolePermissionIds);

            // Si la liste est identique aux permissions du rôle, on n'ajoute pas de permissions directes
            // Cela permet de repasser aux permissions par défaut du rôle (sans surcharge)
            if ($pIds !== $rolePermissionIds) {
                $now = now();
                $records = [];
                foreach ($pIds as $permId) {
                    $records[] = [
                        'user_id' => $userId,
                        'permission_id' => $permId,
                        'granted_by' => $grantedBy,
                        'granted_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (!empty($records)) {
                    UserPermission::insert($records);
                }
            }

            // Notifier l'utilisateur des changements (temps réel)
            EventsController::notifyUserPermissionsChanged($userId);
        });
    }

    private function moduleLabel(string $module): string
    {
        $labels = [
            'comptabilite' => 'Comptabilité',
            'facturation' => 'Facturation',
            'caisse' => 'Caisse',
            'juridique' => 'Juridique',
            'rh' => 'Ressources Humaines',
            'projets' => 'Projets',
            'document' => 'GED (Documents)',
        ];
        return $labels[$module] ?? ucfirst($module);
    }

    private function moduleIcon(string $module): string
    {
        $icons = [
            'comptabilite' => 'bi-calculator',
            'facturation' => 'bi-receipt',
            'caisse' => 'bi-cash-stack',
            'juridique' => 'bi-file-earmark-text',
            'rh' => 'bi-people',
            'projets' => 'bi-kanban',
            'document' => 'bi-folder2-open',
        ];
        return $icons[$module] ?? 'bi-grid-3x3-gap';
    }
}
