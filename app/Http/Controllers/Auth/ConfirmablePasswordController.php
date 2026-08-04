<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Contrôleur de confirmation du mot de passe.
 *
 * Permet de vérifier le mot de passe d'un utilisateur avant d'accéder
 * à des zones sensibles de l'application (ex. paramètres de sécurité).
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Affiche la vue de confirmation du mot de passe.
     */
    public function show(): Response
    {
        return Inertia::render('Auth/ConfirmPassword');
    }

    /**
     * Confirme le mot de passe de l'utilisateur pour une zone sensible.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('home'));
    }
}
