<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyContract;
use App\Models\CompanyLegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LegalController extends Controller
{
    /**
     * Récupère le client_id de l'utilisateur authentifié.
     */
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return $user->client_id;
    }

    /**
     * Affiche la page Juridique.
     */
    public function index()
    {
        $clientId = $this->getClientId();
        return view('company', ['page' => 'company-legal', 'clientId' => $clientId]);
    }

    // ─── CONTRATS ─────────────────────────────────────────────────────────────

    /**
     * API: Liste tous les contrats de l'entreprise.
     */
    public function contracts()
    {
        $clientId = $this->getClientId();

        $contracts = CompanyContract::byClient($clientId)
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id'            => $c->id,
                    'title'         => $c->title,
                    'reference'     => $c->reference,
                    'type'          => $c->type,
                    'party_name'    => $c->party_name,
                    'party_contact' => $c->party_contact,
                    'description'   => $c->description,
                    'start_date'    => $c->start_date?->format('Y-m-d'),
                    'end_date'      => $c->end_date?->format('Y-m-d'),
                    'value'         => $c->value ? (float) $c->value : null,
                    'status'        => $c->status,
                    'file_path'     => $c->file_path,
                    'signed_by'     => $c->signed_by,
                    'signed_at'     => $c->signed_at?->format('Y-m-d H:i'),
                    'created_by'    => $c->createdBy?->name,
                    'created_at'    => $c->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($contracts);
    }

    /**
     * API: Crée un contrat.
     */
    public function storeContract(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'reference'     => 'required|string|max:255|unique:company_contracts,reference',
            'type'          => 'required|in:prestation,nda,licence,emploi,autre',
            'party_name'    => 'required|string|max:255',
            'party_contact' => 'nullable|string',
            'description'   => 'nullable|string',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'value'         => 'nullable|numeric|min:0',
            'status'        => 'required|in:draft,active,expired,terminated',
            'file_path'     => 'nullable|string|max:255',
            'signed_by'     => 'nullable|string|max:255',
            'signed_at'     => 'nullable|date',
        ]);

        $contract = CompanyContract::create(array_merge(
            $validated,
            [
                'client_id'  => $clientId,
                'created_by' => Auth::id(),
            ]
        ));

        return response()->json([
            'message'  => 'Contrat créé avec succès.',
            'contract' => $contract->fresh(),
        ], 201);
    }

    /**
     * API: Modifie un contrat.
     */
    public function updateContract(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $contract = CompanyContract::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'reference'     => 'sometimes|string|max:255|unique:company_contracts,reference,' . $id,
            'type'          => 'sometimes|in:prestation,nda,licence,emploi,autre',
            'party_name'    => 'sometimes|string|max:255',
            'party_contact' => 'sometimes|nullable|string',
            'description'   => 'sometimes|nullable|string',
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|nullable|date|after_or_equal:start_date',
            'value'         => 'sometimes|nullable|numeric|min:0',
            'status'        => 'sometimes|in:draft,active,expired,terminated',
            'file_path'     => 'sometimes|nullable|string|max:255',
            'signed_by'     => 'sometimes|nullable|string|max:255',
            'signed_at'     => 'sometimes|nullable|date',
        ]);

        $contract->update($validated);

        return response()->json([
            'message'  => 'Contrat mis à jour.',
            'contract' => $contract->fresh(),
        ]);
    }

    /**
     * API: Supprime un contrat.
     */
    public function destroyContract($id)
    {
        $clientId = $this->getClientId();

        $contract = CompanyContract::byClient($clientId)->findOrFail($id);
        $contract->delete();

        return response()->json(['message' => 'Contrat supprimé.']);
    }

    // ─── CAS JURIDIQUES (Contentieux) ────────────────────────────────────────

    /**
     * API: Liste tous les cas juridiques de l'entreprise.
     */
    public function cases()
    {
        $clientId = $this->getClientId();

        $cases = CompanyLegalCase::byClient($clientId)
            ->latest()
            ->get()
            ->map(function ($c) {
                return [
                    'id'              => $c->id,
                    'title'           => $c->title,
                    'reference'       => $c->reference,
                    'type'            => $c->type,
                    'status'          => $c->status,
                    'description'     => $c->description,
                    'assigned_to'     => $c->assigned_to,
                    'priority'        => $c->priority,
                    'start_date'      => $c->start_date?->format('Y-m-d'),
                    'resolution_date' => $c->resolution_date?->format('Y-m-d'),
                    'notes'           => $c->notes,
                    'created_by'      => $c->createdBy?->name,
                    'created_at'      => $c->created_at?->format('d/m/Y'),
                ];
            });

        return response()->json($cases);
    }

    /**
     * API: Crée un cas juridique.
     */
    public function storeCase(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'reference'       => 'required|string|max:255',
            'type'            => 'required|in:contentieux,consultation,conseil,autre',
            'status'          => 'required|in:open,in_progress,closed,archived',
            'description'     => 'nullable|string',
            'assigned_to'     => 'nullable|string|max:255',
            'priority'        => 'required|in:low,medium,high,critical',
            'start_date'      => 'required|date',
            'resolution_date' => 'nullable|date|after_or_equal:start_date',
            'notes'           => 'nullable|string',
        ]);

        $case = CompanyLegalCase::create(array_merge(
            $validated,
            [
                'client_id'  => $clientId,
                'created_by' => Auth::id(),
            ]
        ));

        return response()->json([
            'message' => 'Cas juridique créé avec succès.',
            'case'    => $case->fresh(),
        ], 201);
    }

    /**
     * API: Modifie un cas juridique.
     */
    public function updateCase(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $case = CompanyLegalCase::byClient($clientId)->findOrFail($id);

        $validated = $request->validate([
            'title'           => 'sometimes|string|max:255',
            'reference'       => 'sometimes|string|max:255',
            'type'            => 'sometimes|in:contentieux,consultation,conseil,autre',
            'status'          => 'sometimes|in:open,in_progress,closed,archived',
            'description'     => 'sometimes|nullable|string',
            'assigned_to'     => 'sometimes|nullable|string|max:255',
            'priority'        => 'sometimes|in:low,medium,high,critical',
            'start_date'      => 'sometimes|date',
            'resolution_date' => 'sometimes|nullable|date|after_or_equal:start_date',
            'notes'           => 'sometimes|nullable|string',
        ]);

        $case->update($validated);

        return response()->json([
            'message' => 'Cas juridique mis à jour.',
            'case'    => $case->fresh(),
        ]);
    }

    /**
     * API: Supprime un cas juridique.
     */
    public function destroyCase($id)
    {
        $clientId = $this->getClientId();

        $case = CompanyLegalCase::byClient($clientId)->findOrFail($id);
        $case->delete();

        return response()->json(['message' => 'Cas juridique supprimé.']);
    }

    // ─── STATISTIQUES ─────────────────────────────────────────────────────────

    /**
     * API: Statistiques juridiques.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $contracts = CompanyContract::byClient($clientId)->get();
        $cases = CompanyLegalCase::byClient($clientId)->get();

        $totalContracts = $contracts->count();
        $activeContracts = $contracts->where('status', 'active')->count();
        $draftContracts = $contracts->where('status', 'draft')->count();
        $expiredContracts = $contracts->where('status', 'expired')->count();

        $totalCases = $cases->count();
        $openCases = $cases->where('status', 'open')->count();
        $inProgressCases = $cases->where('status', 'in_progress')->count();
        $closedCases = $cases->where('status', 'closed')->count();

        $criticalCases = $cases->whereIn('priority', ['critical'])->whereIn('status', ['open', 'in_progress'])->count();
        $highCases = $cases->whereIn('priority', ['high'])->whereIn('status', ['open', 'in_progress'])->count();

        $totalContractValue = $contracts->whereIn('status', ['active', 'draft'])->sum('value');

        $byContractType = $contracts->groupBy('type')->map(function ($group) {
            return $group->count();
        });

        $byCaseType = $cases->groupBy('type')->map(function ($group) {
            return $group->count();
        });

        $stats = [
            'total_contracts'      => $totalContracts,
            'active_contracts'     => $activeContracts,
            'draft_contracts'      => $draftContracts,
            'expired_contracts'    => $expiredContracts,
            'total_cases'          => $totalCases,
            'open_cases'           => $openCases,
            'in_progress_cases'    => $inProgressCases,
            'closed_cases'         => $closedCases,
            'critical_cases'       => $criticalCases,
            'high_cases'           => $highCases,
            'total_contract_value' => (float) $totalContractValue,
            'by_contract_type'     => $byContractType,
            'by_case_type'         => $byCaseType,
        ];

        return response()->json($stats);
    }
}
