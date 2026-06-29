<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use App\Models\DepreciationSchedule;
use App\Services\AuditTrailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends BaseCompanyController
{
    /**
     * Liste des immobilisations.
     */
    public function index()
    {
        $clientId = $this->getClientId();

        $assets = FixedAsset::where('client_id', $clientId)
            ->with('fiscalYear')
            ->orderBy('acquisition_date', 'desc')
            ->get()
            ->map(function ($a) {
                return [
                    'id'                  => $a->id,
                    'designation'         => $a->designation,
                    'category'            => $a->category,
                    'acquisition_date'    => $a->acquisition_date?->format('Y-m-d'),
                    'gross_value'         => (float) $a->gross_value,
                    'residual_value'      => (float) $a->residual_value,
                    'depreciation_months' => $a->depreciation_months,
                    'depreciation_method' => $a->depreciation_method,
                    'net_book_value'      => (float) $a->net_book_value,
                    'monthly_depr'        => $a->monthlyDepreciation(),
                    'annual_depr'         => $a->annualDepreciation(),
                    'status'              => $a->status,
                    'fiscal_year'         => $a->fiscalYear?->year,
                ];
            });

        return response()->json($assets);
    }

    /**
     * Crée une immobilisation.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'designation'         => 'required|string|max:255',
            'category'            => 'required|in:informatique,mobilier,vehicule,batiment,logiciel,other',
            'acquisition_date'    => 'required|date',
            'gross_value'         => 'required|numeric|min:0',
            'residual_value'      => 'nullable|numeric|min:0',
            'depreciation_months' => 'required|integer|min:1|max:600',
            'depreciation_method' => 'required|in:linear,declining',
            'account_code'        => 'nullable|string|max:20',
            'depreciation_account_code' => 'nullable|string|max:20',
            'notes'               => 'nullable|string',
        ]);

        $netBookValue = $validated['gross_value'];
        $residual = $validated['residual_value'] ?? 0;

        $asset = FixedAsset::create([
            'client_id'              => $clientId,
            'designation'            => $validated['designation'],
            'category'               => $validated['category'],
            'acquisition_date'       => $validated['acquisition_date'],
            'gross_value'            => $validated['gross_value'],
            'residual_value'         => $residual,
            'depreciation_months'    => $validated['depreciation_months'],
            'depreciation_method'    => $validated['depreciation_method'],
            'net_book_value'         => $netBookValue,
            'account_code'           => $validated['account_code'] ?? null,
            'depreciation_account_code' => $validated['depreciation_account_code'] ?? null,
            'notes'                  => $validated['notes'] ?? null,
            'status'                 => 'active',
        ]);

        return response()->json([
            'message' => 'Immobilisation créée.',
            'asset'   => $asset,
        ], 201);
    }

    /**
     * Affiche une immobilisation avec son plan d'amortissement.
     */
    public function show($id)
    {
        $clientId = $this->getClientId();

        $asset = FixedAsset::where('client_id', $clientId)
            ->with('depreciationSchedules')
            ->findOrFail($id);

        $schedule = $asset->depreciationSchedules->map(function ($s) {
            return [
                'id'            => $s->id,
                'period_number' => $s->period_number,
                'period_date'   => $s->period_date?->format('Y-m-d'),
                'amount'        => (float) $s->depreciation_amount,
                'accumulated'   => (float) $s->accumulated_depreciation,
                'net_value'     => (float) $s->net_value,
                'is_booked'     => $s->is_booked,
            ];
        });

        return response()->json([
            'asset'    => $asset,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Met à jour une immobilisation.
     */
    public function update(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $asset = FixedAsset::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'designation'         => 'sometimes|string|max:255',
            'category'            => 'sometimes|in:informatique,mobilier,vehicule,batiment,logiciel,other',
            'gross_value'         => 'sometimes|numeric|min:0',
            'residual_value'      => 'nullable|numeric|min:0',
            'depreciation_months' => 'sometimes|integer|min:1|max:600',
            'depreciation_method' => 'sometimes|in:linear,declining',
            'notes'               => 'nullable|string',
        ]);

        $asset->update($validated);

        return response()->json([
            'message' => 'Immobilisation mise à jour.',
            'asset'   => $asset->fresh(),
        ]);
    }

    /**
     * Génère le plan d'amortissement.
     */
    public function generateSchedule($id)
    {
        $clientId = $this->getClientId();
        $asset = FixedAsset::where('client_id', $clientId)->findOrFail($id);

        // Supprime l'ancien plan
        $asset->depreciationSchedules()->delete();

        $plan = $asset->generateDepreciationPlan();

        $schedules = [];
        foreach ($plan as $entry) {
            $schedules[] = new DepreciationSchedule([
                'fiscal_year_id'            => $asset->fiscal_year_id,
                'period_number'             => $entry['period_number'],
                'period_date'               => $entry['period_date'],
                'depreciation_amount'       => $entry['depreciation_amount'],
                'accumulated_depreciation'  => $entry['accumulated_depreciation'],
                'net_value'                 => $entry['net_value'],
                'is_booked'                 => false,
            ]);
        }

        $asset->depreciationSchedules()->saveMany($schedules);

        $asset->update([
            'net_book_value' => $plan[count($plan) - 1]['net_value'] ?? 0,
        ]);

        AuditTrailService::log($asset, 'schedule_generated', null, null, 'Plan d\'amortissement généré');

        return response()->json([
            'message'  => 'Plan d\'amortissement généré.',
            'schedule' => $asset->depreciationSchedules()->get(),
        ]);
    }

    /**
     * Cession d'une immobilisation.
     */
    public function dispose(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $asset = FixedAsset::where('client_id', $clientId)->where('status', 'active')->findOrFail($id);

        $validated = $request->validate([
            'disposal_date'  => 'required|date',
            'disposal_price' => 'required|numeric|min:0',
            'notes'          => 'nullable|string',
        ]);

        DB::transaction(function () use ($asset, $validated) {
            $accumulated = $asset->accumulatedDepreciation(\Carbon\Carbon::parse($validated['disposal_date']));
            $netValue = $asset->gross_value - $accumulated;
            $gainLoss = $validated['disposal_price'] - $netValue;

            $asset->update([
                'status'           => 'sold',
                'disposal_date'    => $validated['disposal_date'],
                'disposal_price'   => $validated['disposal_price'],
                'net_book_value'   => max($netValue, 0),
                'capital_gain_loss'=> round($gainLoss, 2),
                'notes'            => $validated['notes'] ?? $asset->notes,
            ]);
        });

        AuditTrailService::log($asset, 'disposed', null, $asset->toArray(), 'Cession d\'immobilisation');

        return response()->json([
            'message' => 'Cession enregistrée.',
            'asset'   => $asset->fresh(),
        ]);
    }

    /**
     * Supprime une immobilisation.
     */
    public function destroy($id)
    {
        $clientId = $this->getClientId();
        $asset = FixedAsset::where('client_id', $clientId)->findOrFail($id);
        $asset->depreciationSchedules()->delete();
        $asset->delete();

        return response()->json(['message' => 'Immobilisation supprimée.']);
    }
}
