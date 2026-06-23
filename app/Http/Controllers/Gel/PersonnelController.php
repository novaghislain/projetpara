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
     * Affiche la page de gestion du personnel GEL.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-personnel']);
    }

    /**
     * API: Liste tout le personnel GEL (utilisateurs internes du cabinet).
     */
    public function listAll()
    {
        // Personnel GEL = pas client_id (pas rattaché à une entreprise cliente)
        // et rôle interne au cabinet
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

        // Rôles disponibles pour le personnel GEL
        $availableRoles = Role::whereNotIn('slug', ['super_admin', 'company_admin', 'client'])
            ->orderBy('level', 'desc')
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json([
            'staff' => $staff,
            'roles' => $availableRoles,
        ]);
    }

    /**
     * API: Crée un membre du personnel GEL.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'fonction' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        // Vérifier que le rôle est autorisé pour le personnel GEL
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

        // Si le rôle est secretaire, activer le flag role_secretaire
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
     * API: Met à jour un membre du personnel GEL.
     */
    public function update(Request $request, $id)
    {
        $user = User::whereNull('client_id')->findOrFail($id);

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

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Si le rôle change, mettre à jour le champ role et le flag secretaire
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
     * API: Supprime un membre du personnel GEL.
     */
    public function destroy($id)
    {
        $user = User::whereNull('client_id')->findOrFail($id);

        if ((int) $user->id === (int) Auth::id()) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'Membre du personnel supprimé.']);
    }
}
