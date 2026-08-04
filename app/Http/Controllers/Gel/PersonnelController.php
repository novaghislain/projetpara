<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PersonnelController extends Controller
{
    /**
     * Contrôleur de gestion du personnel interne du cabinet GEL.
     * Gère les utilisateurs internes (non clients) avec un système
     * de hiérarchie pour les droits de modification/suppression.
     */

    /**
     * Vérifie les droits d'accès à la gestion du personnel.
     * Seuls les super-admins, RH et directeurs peuvent gérer le personnel.
     * La hiérarchie empêche la modification d'utilisateurs de niveau supérieur.
     *
     * @param object|null $targetUser L'utilisateur cible (optionnel)
     * @return void
     */
    private function authorizeManager($targetUser = null): void
    {
        $currentUser = Auth::user();

        // Les employés d'entreprise n'ont pas accès à la gestion du personnel
        if ($currentUser->client_id !== null) {
            abort(403, 'Accès réservé au personnel interne du cabinet.');
        }

        // Vérification des rôles autorisés à gérer le personnel
        if (!$currentUser->isSuperAdmin() && !in_array($currentUser->role, ['rh', 'director'])) {
            abort(403, 'Vous n\'avez pas les droits pour gérer le personnel.');
        }

        // Protection hiérarchique pour la modification/suppression
        if ($targetUser && !$currentUser->isSuperAdmin()) {
            $hierarchy = [
                'collaborator' => 0, 'secretaire' => 0, 'juriste' => 0,
                'gestionnaire_projet' => 0, 'comptable' => 0,
                'rh' => 1, 'pole_responsible' => 1,
                'director' => 2, 'super_admin' => 3
            ];

            $currentLevel = $hierarchy[$currentUser->role] ?? 0;
            $targetLevel = $hierarchy[$targetUser->role] ?? 0;

            // Un utilisateur ne peut modifier que des utilisateurs de niveau strictement inférieur
            // ou son propre compte
            if ($targetLevel >= $currentLevel && $currentUser->id !== $targetUser->id) {
                abort(403, 'Vous ne pouvez pas modifier ou supprimer cet utilisateur (privilèges insuffisants).');
            }
        }
    }

    /**
     * Affiche la page de gestion du personnel GEL.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorizeManager();
        return view('app', ['page' => 'gel-personnel']);
    }

    /**
     * API : Liste tout le personnel GEL (utilisateurs internes du cabinet).
     *
     * @return \Illuminate\Http\JsonResponse La liste du personnel et les rôles disponibles
     */
    public function listAll()
    {
        $this->authorizeManager();

        // Personnel GEL = utilisateurs sans client_id (non rattachés à une entreprise cliente)
        // avec un rôle interne au cabinet
        $staff = User::whereNull('client_id')
            ->whereNotIn('role', ['company_admin', 'client'])
            ->with('roleModel')
            ->latest()
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role,
                    'role_name' => $u->roleModel?->name ?? $u->role,
                    'fonction' => $u->fonction,
                    'is_active' => $u->is_active,
                    'phone' => $u->phone,
                    'role_secretaire' => $u->role_secretaire,
                    'last_login' => $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Jamais',
                    'created_at' => $u->created_at?->format('d/m/Y'),
                ];
            });

        // Rôles disponibles pour le personnel GEL (exclusion des rôles entreprise)
        $availableRoles = Role::whereNotIn('slug', ['super_admin', 'company_admin', 'client'])
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'staff' => $staff,
            'roles' => $availableRoles,
        ]);
    }

    /**
     * API : Crée un nouveau membre du personnel GEL.
     *
     * @param Request $request La requête HTTP avec les données du membre
     * @return \Illuminate\Http\JsonResponse Le membre créé
     */
    public function store(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'fonction' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Vérification que le rôle est autorisé pour le personnel GEL
        if ($validated['role_id'] ?? null) {
            $role = Role::findOrFail($validated['role_id']);
            if (in_array($role->slug, ['super_admin', 'company_admin', 'client'])) {
                return response()->json(['message' => 'Ce rôle ne peut pas être attribué au personnel GEL.'], 403);
            }
        }

        $roleSlug = $validated['role_id'] ? Role::find($validated['role_id'])->slug : 'collaborator';

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $roleSlug,
            'role_id' => $validated['role_id'] ?? null,
            'fonction' => $validated['fonction'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
            'client_id' => null, // Personnel GEL, pas d'entreprise cliente
        ]);

        // Activation du flag secretaire si le rôle l'exige
        if ($roleSlug === 'secretaire') {
            $user->role_secretaire = true;
            $user->save();
        }

        return response()->json([
            'message' => 'Membre du personnel créé avec succès.',
            'user' => $user->fresh()->load('roleModel'),
        ], 201);
    }

    /**
     * API : Met à jour un membre du personnel GEL.
     *
     * @param Request $request La requête HTTP avec les données mises à jour
     * @param int $id L'identifiant du membre
     * @return \Illuminate\Http\JsonResponse Le membre mis à jour
     */
    public function update(Request $request, $id)
    {
        $user = User::whereNull('client_id')->findOrFail($id);
        $this->authorizeManager($user);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|nullable|exists:roles,id',
            'fonction' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = collect($validated)->except('password')->toArray();

        // Hachage du mot de passe si fourni
        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Gestion du changement de rôle et du flag secretaire
        if (isset($validated['role_id'])) {
            if ($validated['role_id']) {
                $role = Role::find($validated['role_id']);
                if ($role && in_array($role->slug, ['super_admin', 'company_admin', 'client'])) {
                    return response()->json(['message' => 'Ce rôle ne peut pas être attribué.'], 403);
                }
                if ($role) {
                    $data['role'] = $role->slug;
                    $data['role_secretaire'] = ($role->slug === 'secretaire');
                }
            } else {
                $data['role'] = 'collaborator';
                $data['role_secretaire'] = false;
            }
        }

        $user->update($data);

        return response()->json([
            'message' => 'Membre du personnel mis à jour.',
            'user' => $user->fresh()->load('roleModel'),
        ]);
    }

    /**
     * API : Supprime un membre du personnel GEL.
     * Empêche la suppression de son propre compte.
     *
     * @param int $id L'identifiant du membre
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy($id)
    {
        $user = User::whereNull('client_id')->findOrFail($id);
        $this->authorizeManager($user);

        // Protection contre l'auto-suppression
        if ((int) $user->id === (int) Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Membre du personnel supprimé.']);
    }
}
