<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FiscalPeriod;
use App\Models\FiscalYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FiscalPeriodController extends Controller
{
    protected function getClientId(): int
    {
        return (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);
    }

    /**
     * Périodes d'un exercice.
     */
    public function index(int $fiscalYear): JsonResponse
    {
        $clientId = $this->getClientId();

        $fiscalYearModel = FiscalYear::where('client_id', $clientId)->findOrFail($fiscalYear);

        $periods = FiscalPeriod::where('fiscal_year_id', $fiscalYear)
            ->with('closedBy:id,name')
            ->orderBy('start_date')
            ->get();

        return response()->json($periods);
    }

    /**
     * Créer une période.
     */
    public function store(Request $request, int $fiscalYear): JsonResponse
    {
        $clientId = $this->getClientId();

        $fiscalYearModel = FiscalYear::where('client_id', $clientId)->findOrFail($fiscalYear);

        if ($fiscalYearModel->isClosed()) {
            return response()->json(['message' => 'Impossible d\'ajouter une période à un exercice clôturé.'], 409);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10'],
            'label' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $period = DB::transaction(function () use ($fiscalYear, $validated) {
            $data = array_merge($validated, [
                'fiscal_year_id' => $fiscalYear,
                'status' => 'open',
            ]);

            return FiscalPeriod::create($data);
        });

        return response()->json([
            'message' => 'Période créée avec succès.',
            'period' => $period,
        ], 201);
    }

    /**
     * Générer les 12 périodes mensuelles d'un exercice.
     */
    public function generateMonthly(int $fiscalYear): JsonResponse
    {
        $clientId = $this->getClientId();
        $fiscalYearModel = FiscalYear::where('client_id', $clientId)->findOrFail($fiscalYear);

        if ($fiscalYearModel->isClosed()) {
            return response()->json(['message' => 'Exercice clôturé.'], 409);
        }

        $existingCount = FiscalPeriod::where('fiscal_year_id', $fiscalYear)->count();
        if ($existingCount > 0) {
            return response()->json(['message' => 'Des périodes existent déjà pour cet exercice.'], 409);
        }

        $months = [
            ['code' => 'M01', 'label' => 'Janvier'],
            ['code' => 'M02', 'label' => 'Février'],
            ['code' => 'M03', 'label' => 'Mars'],
            ['code' => 'M04', 'label' => 'Avril'],
            ['code' => 'M05', 'label' => 'Mai'],
            ['code' => 'M06', 'label' => 'Juin'],
            ['code' => 'M07', 'label' => 'Juillet'],
            ['code' => 'M08', 'label' => 'Août'],
            ['code' => 'M09', 'label' => 'Septembre'],
            ['code' => 'M10', 'label' => 'Octobre'],
            ['code' => 'M11', 'label' => 'Novembre'],
            ['code' => 'M12', 'label' => 'Décembre'],
        ];

        $year = $fiscalYearModel->year;
        $periods = DB::transaction(function () use ($fiscalYear, $year, $months) {
            $created = [];
            foreach ($months as $i => $month) {
                $monthNum = $i + 1;
                $startDate = sprintf('%d-%02d-01', $year, $monthNum);
                $endDate = date('Y-m-t', strtotime($startDate));

                $created[] = FiscalPeriod::create([
                    'fiscal_year_id' => $fiscalYear,
                    'code' => $month['code'],
                    'label' => $month['label'] . ' ' . $year,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'open',
                ]);
            }
            return $created;
        });

        return response()->json([
            'message' => '12 périodes mensuelles générées.',
            'periods' => $periods,
        ], 201);
    }

    /**
     * Clôturer une période.
     */
    public function close(int $fiscalYear, int $period): JsonResponse
    {
        $clientId = $this->getClientId();
        $fiscalYearModel = FiscalYear::where('client_id', $clientId)->findOrFail($fiscalYear);

        $periodModel = FiscalPeriod::where('fiscal_year_id', $fiscalYear)
            ->findOrFail($period);

        if (!$periodModel->isOpen()) {
            return response()->json(['message' => 'Période déjà clôturée.'], 409);
        }

        $periodModel->close();

        return response()->json([
            'message' => 'Période clôturée avec succès.',
            'period' => $periodModel->fresh()->load('closedBy:id,name'),
        ]);
    }
}
