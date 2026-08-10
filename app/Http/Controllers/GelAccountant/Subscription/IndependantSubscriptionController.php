<?php

namespace App\Http\Controllers\GelAccountant\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IndependantSubscriptionController extends Controller
{
    /**
     * Affiche les offres d'abonnement pour le comptable indépendant.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->isAutonomousAccountant()) {
            abort(403, 'Accès réservé aux comptables indépendants.');
        }

        $plans = DB::table('subscription_plans')
                   ->where('profile_type', 'comptable_independant')
                   ->where('is_active', true)
                   ->get();
        
        return view('gel-accountant.independant.subscription.index', compact('user', 'plans'));
    }

    /**
     * Traite la souscription à une offre (Simulation paiement Mobile Money / CB).
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAutonomousAccountant()) {
            abort(403);
        }

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|string'
        ]);

        // Simuler le paiement réussi et l'activation de l'abonnement
        // Dans un cas réel, cela passerait par FedaPay / Kkiapay / Stripe
        $user->update([
            'subscription_status' => 'active',
            'plan_id' => $validated['plan_id']
        ]);

        return redirect()->route('gel-accountant.dashboard')
            ->with('success', 'Votre abonnement a été activé avec succès. Merci pour votre confiance !');
    }
}
