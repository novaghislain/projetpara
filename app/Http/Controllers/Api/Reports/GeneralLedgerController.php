<?php
namespace App\Http\Controllers\Api\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\GeneralLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralLedgerController extends Controller
{
    private GeneralLedgerService $ledgerService;

    public function __construct(GeneralLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Grand livre d'un compte spécifique
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
     * Grand livre par classe de comptes
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
