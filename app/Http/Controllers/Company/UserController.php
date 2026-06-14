<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Vérifie que l'utilisateur est bien administrateur de son entreprise.
     */
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
     * API: Liste tous les utilisateurs de l'entreprise avec les rôles disponibles.
     */
    public function listAll()
    {
        $this->authorizeAdmin();

        $user = Auth::user();
        if (!$user->client_id) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 403);
        }

        $users = User::where('client_id', $user->client_id)
            ->with('roleModel')
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
                    'created_at' => $u->created_at?->format('d/m/Y'),
                ];
            });

        // Rôles disponibles pour l'entreprise (exclure super_admin et company_admin)
        $availableRoles = Role::whereNotIn('slug', ['super_admin', 'company_admin'])
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'users' => $users,
            'roles' => $availableRoles,
        ]);
    }

    /**
     * Retourne un utilisateur spécifique.
     */
    public function show($id)
    {
        $this->authorizeAdmin();
        $user = Auth::user();
        $target = User::where('client_id', $user->client_id)
            ->with('roleModel')
            ->findOrFail($id);

        return response()->json([
            'id' => $target->id,
            'name' => $target->name,
            'email' => $target->email,
            'fonction' => $target->fonction,
            'role_id' => $target->role_id,
            'role_name' => $target->roleModel?->name ?? 'N/A',
            'is_active' => $target->is_active,
        ]);
    }

    /**
     * Crée un nouvel utilisateur dans l'entreprise.
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $user = Auth::user();
        if (!$user->client_id) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'fonction' => 'nullable|string|max:255',
        ]);

        // Vérifier que le rôle n'est pas interdit (super_admin, company_admin)
        $role = Role::findOrFail($validated['role_id']);
        if (in_array($role->slug, ['super_admin', 'company_admin'])) {
            return response()->json(['message' => 'Ce rôle ne peut pas être attribué.'], 403);
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'client_id' => $user->client_id,
            'role_id' => $validated['role_id'],
            'role' => $role->slug,
            'fonction' => $validated['fonction'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => [
                'id' => $newUser->id,
                'name' => $newUser->name,
                'email' => $newUser->email,
                'role_name' => $role->name,
                'fonction' => $newUser->fonction,
            ],
        ], 201);
    }

    /**
     * Met à jour un utilisateur de l'entreprise.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $user = Auth::user();
        $target = User::where('client_id', $user->client_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target->id)],
            'password' => 'sometimes|nullable|string|min:8',
            'role_id' => 'sometimes|exists:roles,id',
            'fonction' => 'sometimes|nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = collect($validated)->except('password')->toArray();

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Si le rôle change, mettre à jour aussi le champ `role` (string)
        if (isset($validated['role_id'])) {
            $role = Role::find($validated['role_id']);
            if ($role && in_array($role->slug, ['super_admin', 'company_admin'])) {
                return response()->json(['message' => 'Ce rôle ne peut pas être attribué.'], 403);
            }
            if ($role) {
                $data['role'] = $role->slug;
            }
        }

        $target->update($data);

        return response()->json([
            'message' => 'Utilisateur mis à jour.',
            'user' => $target->fresh()->load('roleModel'),
        ]);
    }

    /**
     * Supprime un utilisateur de l'entreprise.
     */
    public function destroy($id)
    {
        $this->authorizeAdmin();
        $user = Auth::user();
        $target = User::where('client_id', $user->client_id)->findOrFail($id);

        // Empêcher l'auto-suppression
        if ((int) $target->id === (int) $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $target->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
