<?php

namespace App\Http\Controllers\GelSecretary\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Affiche la page de blocage pour abonnement expiré.
     */
    public function expired(Request $request)
    {
        $user = $request->user();

        // Si l'abonnement est actif, rediriger vers le dashboard
        if ($user->hasActiveSubscription()) {
            return redirect()->route('gel-secretary.dashboard');
        }

        return view('gel-secretary.subscription.expired', compact('user'));
    }
}
