<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur de renvoi des notifications de vérification d'email.
 *
 * Permet à l'utilisateur de renvoyer un lien de vérification
 * d'adresse email si le précédent a expiré ou n'a pas été reçu.
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Renvoie une nouvelle notification de vérification d'email.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
