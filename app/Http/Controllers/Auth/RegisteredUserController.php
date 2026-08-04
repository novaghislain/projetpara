<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

/**
 * Contrôleur d'inscription publique des utilisateurs.
 *
 * Gère la création d'un nouveau compte utilisateur avec le rôle 'client'
 * et la connexion automatique après inscription.
 */
class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    /**
     * Enregistre un nouvel utilisateur avec le rôle 'client'.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'client', // les inscriptions publiques sont des clients
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Si le client venait du catalogue (service en session), on le renvoie commander
        if (session('order_service_id')) {
            return redirect()->route('commande.step');
        }

        // Sinon, vers son espace client
        return redirect()->route('client.orders.index');
    }
}
