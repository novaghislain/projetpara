<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Contrôleur d'envoi du lien de réinitialisation du mot de passe.
 *
 * Affiche le formulaire de demande et envoie un email
 * contenant un lien de réinitialisation du mot de passe.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Affiche la vue de demande de lien de réinitialisation.
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.forgot-password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Traite la demande d'envoi d'un lien de réinitialisation.
     *
     * @param  Request  $request  La requête HTTP entrante
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
