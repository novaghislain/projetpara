<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur API du grand livre comptable.
 *
 * Affiche le détail des mouvements d'un compte spécifique
 * ou d'une classe de comptes sur une période donnée.
 */
class GeneralLedgerController extends Controller
{
    private GeneralLedgerService $ledgerService;

    /**
     * Constructeur avec injection du service de grand livre.
     */
    public function __construct(GeneralLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Affiche les mouvements d'un compte spécifique (grand livre) avec pagination.
     *
     * @param Request $request La requête HTTP avec la période et la pagination.
     * @param int $accountId L'identifiant du compte comptable.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $accountId)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->ledgerService->getLedger(
            $clientId,
            $accountId,
            $request->input('start_date'),
            $request->input('end_date'),
            (int) $request->input('per_page', 50)
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Affiche le grand livre pour tous les comptes d'une classe SYSCOHADA.
     *
     * @param Request $request La requête HTTP avec la période.
     * @param string $classCode Le code de la classe (ex: 1, 2, 3... 9).
     * @return \Illuminate\Http\JsonResponse
     */
    public function byClass(Request $request, string $classCode)
    {
        $clientId = (int) (Auth::user()->active_client_id ?? Auth::user()->client_id);

        $data = $this->ledgerService->getClassLedger(
            $clientId,
            $classCode,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
