<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Contrôleur d'affichage de l'invite de vérification d'email.
 *
 * Affiche une page invitant l'utilisateur à vérifier son adresse email
 * si elle ne l'est pas encore, ou le redirige vers la page d'accueil.
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Affiche l'invite de vérification d'email ou redirige si déjà vérifié.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('home'))
                    : Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
