<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyAdminController extends Controller
{
    /**
     * Affiche la page de gestion des administrateurs entreprise.
     */
    public function index()
    {
        return view('app', ['page' => 'gel-company-admins']);
    }

    /**
     * API: Retourne tous les utilisateurs qui sont administrateurs entreprise
     * (is_company_admin = true OU client_id non nul), avec leur relation client.
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
     * API: Retourne un administrateur entreprise spécifique.
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
     * API: Crée un nouvel administrateur entreprise.
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

        return response()->json($user->load('client'), 201);
    }

    /**
     * API: Met à jour un administrateur entreprise.
     * Ne change pas le mot de passe si le champ est vide.
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
     * API: Supprime un administrateur entreprise (seulement s'il est company_admin).
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
