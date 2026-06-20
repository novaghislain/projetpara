<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhAlert;
use Illuminate\Http\Request;

class RhAlertsController extends Controller
{
    protected function getClientId(Request $request)
    {
        return $request->input('client_id') ?: Auth::user()?->client_id;
    }

    public function listAll(Request $request)
    {
        $query = RhAlert::byClient($this->getClientId($request))
            ->with('employee')
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return response()->json($query->paginate(20));
    }

    public function changerStatut(Request $request, $id)
    {
        $alert = RhAlert::byClient($this->getClientId($request))->findOrFail($id);
        $validated = $request->validate([
            'statut' => 'required|string|in:active,desactivee,resolue,ignoree',
        ]);
        $alert->update($validated);

        return response()->json($alert);
    }

    public function generate(Request $request)
    {
        $clientId = $this->getClientId($request);
        $employeeIds = \App\Models\Rh\RhEmployee::byClient($clientId)->pluck('id');

        $generated = 0;

        // Vérifier les contrats expirant dans 30 jours
        $expiringContracts = \App\Models\Rh\RhContract::whereIn('employee_id', $employeeIds)
            ->where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [now(), now()->addDays(30)])
            ->get();

        foreach ($expiringContracts as $contract) {
            $existing = RhAlert::where('client_id', $clientId)
                ->where('type', 'contrat_fin')
                ->where('employee_id', $contract->employee_id)
                ->where('statut', 'active')
                ->first();

            if (!$existing) {
                RhAlert::create([
                    'client_id' => $clientId,
                    'employee_id' => $contract->employee_id,
                    'type' => 'contrat_fin',
                    'titre' => "Fin de contrat - {$contract->employee?->full_name}",
                    'description' => "Le contrat de {$contract->employee?->full_name} expire le {$contract->date_fin->format('d/m/Y')}.",
                    'date_echeance' => $contract->date_fin,
                    'statut' => 'active',
                    'days_before' => 30,
                ]);
                $generated++;
            }
        }

        // Alertes périodes d'essai expirant
        $expiringTrials = \App\Models\Rh\RhContract::whereIn('employee_id', $employeeIds)
            ->where('statut', 'actif')
            ->whereNotNull('date_fin_periode_essai')
            ->whereBetween('date_fin_periode_essai', [now(), now()->addDays(14)])
            ->get();

        foreach ($expiringTrials as $contract) {
            $existing = RhAlert::where('client_id', $clientId)
                ->where('type', 'periode_essai')
                ->where('employee_id', $contract->employee_id)
                ->where('statut', 'active')
                ->first();

            if (!$existing) {
                RhAlert::create([
                    'client_id' => $clientId,
                    'employee_id' => $contract->employee_id,
                    'type' => 'periode_essai',
                    'titre' => "Fin période d'essai - {$contract->employee?->full_name}",
                    'description' => "La période d'essai de {$contract->employee?->full_name} se termine le {$contract->date_fin_periode_essai->format('d/m/Y')}.",
                    'date_echeance' => $contract->date_fin_periode_essai,
                    'statut' => 'active',
                    'days_before' => 14,
                ]);
                $generated++;
            }
        }

        return response()->json(['message' => "{$generated} alertes générées.", 'generated' => $generated]);
    }
}
