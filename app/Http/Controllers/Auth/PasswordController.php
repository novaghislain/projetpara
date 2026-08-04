<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Contrôleur de mise à jour du mot de passe.
 *
 * Permet à l'utilisateur authentifié de modifier son mot de passe
 * en vérifiant d'abord le mot de passe actuel.
 */
class PasswordController extends Controller
{
    /**
     * Met à jour le mot de passe de l'utilisateur connecté.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
