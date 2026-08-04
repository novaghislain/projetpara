<?php

namespace App\Http\Controllers\GelAdmin\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\GelAdmin\CabinetSubscriptionInvoice;

class PaymentController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function show(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $plan_id = $request->query('plan_id');
        // Logic to get plan details from plan_id
        $amount = 50000; // Mock amount
        
        return view('gel-admin.subscription.payment', compact('cabinet', 'plan_id', 'amount'));
    }

    public function process(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        $request->validate([
            'phone' => 'required|string',
            'plan_id' => 'required|string',
        ]);

        // MOCK PAYMENT via Mobile Money
        $isSuccess = true;

        if ($isSuccess) {
            // Créer la facture
            if ($type === 'cabinet') {
                CabinetSubscriptionInvoice::create([
                    'cabinet_id' => $cabinet->id,
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    'amount' => 50000,
                    'status' => 'paid',
                    'plan_name' => $request->plan_id,
                    'paid_at' => now(),
                ]);
            }

            // Mettre à jour l'abonnement
            $user = Auth::user();
            $user->subscription_status = 'active';
            $user->plan_id = $request->plan_id;
            $user->save();

            return redirect()->route('gel-admin.subscription.index')->with('success', 'Paiement effectué avec succès.');
        }

        return back()->with('error', 'Échec du paiement.');
    }
}
