<?php

namespace App\Http\Controllers\Company;

use App\Models\Client;
use App\Models\License;
use App\Models\Service;
use App\Models\Gel\GelFacture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends BaseCompanyController
{
    private function authorizeAdmin(): void
    {
        $user = Auth::user();
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Accès réservé à l\'administrateur.');
        }
    }

    public function getCurrentSubscription()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $client = Client::with(['activeLicenses.service'])->findOrFail($clientId);

        $monthlyTotal = $client->activeLicenses->sum('price');
        
        $subscription = [
            'modules' => $client->activeLicenses->map(function ($license) {
                return [
                    'id' => $license->id,
                    'service_name' => $license->service->name ?? 'Service',
                    'service_id' => $license->service_id,
                    'price' => $license->price,
                    'start_date' => $license->start_date?->format('d/m/Y'),
                    'end_date' => $license->end_date?->format('d/m/Y'),
                ];
            }),
            'monthly_total' => $monthlyTotal,
            'next_billing_date' => now()->addMonth()->startOfMonth()->format('d/m/Y') // Simple mockup for next billing date
        ];

        return response()->json(['subscription' => $subscription]);
    }

    public function toggleModule(Request $request)
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'action' => 'required|in:add,remove'
        ]);

        $service = Service::findOrFail($validated['service_id']);

        if ($validated['action'] === 'add') {
            // Check if already has active license
            $existing = License::where('client_id', $clientId)
                ->where('service_id', $service->id)
                ->active()
                ->first();
                
            if ($existing) {
                return response()->json(['message' => 'Module déjà actif.'], 400);
            }
            
            // Add license
            $license = License::create([
                'client_id' => $clientId,
                'service_id' => $service->id,
                'duration_months' => 1,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'price' => $service->price ?? 0, // Assumption
                'status' => 'active'
            ]);
            
            $message = 'Module ajouté avec succès.';
        } else {
            // Remove license
            $license = License::where('client_id', $clientId)
                ->where('service_id', $service->id)
                ->active()
                ->first();
                
            if (!$license) {
                return response()->json(['message' => 'Module non actif.'], 400);
            }
            
            $license->status = 'cancelled';
            $license->save();
            
            $message = 'Module retiré avec succès.';
        }
        
        // Calcul du prorata mockup
        $prorata = 0; // The logic for prorata will be added if required by the billing engine

        return response()->json([
            'message' => $message,
            'prorata' => $prorata
        ]);
    }

    public function getInvoices()
    {
        $this->authorizeAdmin();
        $clientId = $this->getClientId();

        if (class_exists(GelFacture::class)) {
            $invoices = GelFacture::where('client_id', $clientId)
                ->orderByDesc('date_facture')
                ->get();
        } else {
            $invoices = [];
        }

        return response()->json(['invoices' => $invoices]);
    }
}
