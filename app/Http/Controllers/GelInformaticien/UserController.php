<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('gel-informaticien.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'account_type' => 'required|string',
            'role' => 'required|string',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'account_type' => $request->account_type,
            'role' => $request->role,
            'workspace_type' => 'entreprise',
            'is_active' => true,
        ]);

        return redirect()->route('gel-informaticien.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'account_type' => 'required|string',
            'role' => 'required|string',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'account_type' => $request->account_type,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('gel-informaticien.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('gel-informaticien.users.index')->with('error', 'Vous ne pouvez pas vous supprimer vous-même.');
        }

        $user->delete();
        return redirect()->route('gel-informaticien.users.index')->with('success', 'Utilisateur supprimé.');
    }
}
