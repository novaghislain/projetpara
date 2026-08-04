<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Contrôleur de vérification de l'adresse email.
 *
 * Marque l'adresse email de l'utilisateur comme vérifiée après
 * que celui-ci a cliqué sur le lien de vérification reçu par email.
 */
class VerifyEmailController extends Controller
{
    /**
     * Valide et marque l'adresse email de l'utilisateur comme vérifiée.
     *
     * @param  EmailVerificationRequest  $request  La requête de vérification d'email
     * @return RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
