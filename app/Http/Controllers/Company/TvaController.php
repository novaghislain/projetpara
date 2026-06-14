<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingJournalLine;
use App\Models\FiscalYear;
use App\Models\TvaDeclaration;
use App\Services\AuditTrailService;
use App\Services\FiscalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvaController extends Controller
{
    private function getClientId()
    {
        $user = Auth::user();
        if (!$user->client_id) abort(403, 'Aucune entreprise associée.');
        return $user->client_id;
    }

    /**
     * Liste des déclarations TVA.
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $declarations = TvaDeclaration::where('client_id', $clientId)
            ->with('fiscalYear', 'createdBy')
            ->orderBy('period', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id'              => $d->id,
                    'period'          => $d->period,
                    'type'            => $d->type,
                    'tva_collected'   => (float) $d->tva_collected,
                    'tva_deductible'  => (float) $d->tva_deductible,
                    'tva_net'         => (float) $d->tva_net,
                    'status'          => $d->status,
                    'submitted_at'    => $d->submitted_at?->format('d/m/Y'),
                    'approved_at'     => $d->approved_at?->format('d/m/Y'),
                    'fiscal_year'     => $d->fiscalYear?->year,
                    'created_by'      => $d->createdBy?->name,
                ];
            });

        return response()->json($declarations);
    }

    /**
     * Calcule la TVA d'une période à partir des écritures comptables.
     */
    public function compute(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'period'         => 'required|regex:/^\d{4}-\d{2}$/',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
        ]);

        [$year, $month] = explode('-', $validated['period']);

        $lines = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId, $year, $month) {
            $q->where('client_id', $clientId)
              ->where('status', 'posted')
              ->whereYear('entry_date', $year)
              ->whereMonth('entry_date', $month);
        })->whereNotNull('tva_code')->get();

        $result = FiscalService::computeTvaDeclaration($lines);

        $result['period'] = $validated['period'];
        $result['lines_count'] = $lines->count();
        $result['lines'] = $lines->map(function ($l) {
            return [
                'id'         => $l->id,
                'label'      => $l->label,
                'tva_code'   => $l->tva_code,
                'tva_rate'   => (float) $l->tva_rate,
                'tva_amount' => (float) $l->tva_amount,
                'tva_type'   => $l->tva_type,
                'debit'      => (float) $l->debit,
                'credit'     => (float) $l->credit,
            ];
        });

        return response()->json($result);
    }

    /**
     * Crée une déclaration TVA.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'period'         => 'required|regex:/^\d{4}-\d{2}$/',
            'type'           => 'required|in:monthly,quarterly,annual',
            'fiscal_year_id' => 'nullable|exists:fiscal_years,id',
            'tva_collected'  => 'required|numeric|min:0',
            'tva_deductible' => 'required|numeric|min:0',
            'tva_net'        => 'required|numeric',
            'details'        => 'nullable|array',
            'notes'          => 'nullable|string',
        ]);

        $exists = TvaDeclaration::where('client_id', $clientId)
            ->where('period', $validated['period'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Une déclaration existe déjà pour cette période.'], 422);
        }

        $declaration = TvaDeclaration::create([
            'client_id'      => $clientId,
            'fiscal_year_id' => $validated['fiscal_year_id'] ?? null,
            'period'         => $validated['period'],
            'type'           => $validated['type'],
            'tva_collected'  => $validated['tva_collected'],
            'tva_deductible' => $validated['tva_deductible'],
            'tva_net'        => $validated['tva_net'],
            'details'        => $validated['details'] ?? null,
            'status'         => 'draft',
            'created_by'     => Auth::id(),
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Déclaration TVA créée.',
            'declaration' => $declaration,
        ], 201);
    }

    /**
     * Affiche une déclaration.
     */
    public function show($id)
    {
        $clientId = $this->getClientId();
        $d = TvaDeclaration::where('client_id', $clientId)
            ->with('fiscalYear', 'createdBy')
            ->findOrFail($id);

        return response()->json(['declaration' => $d]);
    }

    /**
     * Soumet une déclaration pour approbation.
     */
    public function submit($id)
    {
        $clientId = $this->getClientId();
        $d = TvaDeclaration::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $d->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        AuditTrailService::log($d, 'submitted', null, $d->toArray(), 'Déclaration TVA soumise');

        return response()->json(['message' => 'Déclaration soumise.', 'declaration' => $d->fresh()]);
    }

    /**
     * Approuve une déclaration.
     */
    public function approve($id)
    {
        $clientId = $this->getClientId();
        $d = TvaDeclaration::where('client_id', $clientId)
            ->where('status', 'submitted')
            ->findOrFail($id);

        $d->update([
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        AuditTrailService::log($d, 'approved', null, $d->toArray(), 'Déclaration TVA approuvée');

        return response()->json(['message' => 'Déclaration approuvée.', 'declaration' => $d->fresh()]);
    }

    /**
     * Marque comme payée.
     */
    public function markPaid($id)
    {
        $clientId = $this->getClientId();
        $d = TvaDeclaration::where('client_id', $clientId)
            ->whereIn('status', ['approved', 'submitted'])
            ->findOrFail($id);

        $d->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json(['message' => 'Déclaration marquée comme payée.', 'declaration' => $d->fresh()]);
    }

    /**
     * Supprime une déclaration.
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();
        $d = TvaDeclaration::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $d->delete();
        return response()->json(['message' => 'Déclaration supprimée.']);
    }
}
