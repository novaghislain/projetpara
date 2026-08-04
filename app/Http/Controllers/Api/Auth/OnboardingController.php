<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\ChartAccount;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\BankAccount;
use Illuminate\Http\Request;

/**
 * Contrôleur API d'onboarding des nouveaux locataires.
 *
 * Vérifie l'état d'avancement de la configuration initiale
 * (plan comptable, journaux, écritures, banque) et suggère
 * les premières actions à réaliser.
 */
class OnboardingController extends Controller
{
    /**
     * Vérifie si le tenant (entreprise) est initialisé (première connexion).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun tenant associé.',
            ], 400);
        }

        $hasEntries = JournalEntry::where('tenant_id', $tenantId)->exists();
        $hasBankAccounts = BankAccount::where('tenant_id', $tenantId)->exists();

        $steps = [
            'accounting_plan' => ChartAccount::where('tenant_id', $tenantId)->count() > 0,
            'journals'        => Journal::where('tenant_id', $tenantId)->count() > 0,
            'first_entry'     => $hasEntries,
            'bank_account'    => $hasBankAccounts,
        ];

        $completed = array_filter($steps);
        $total     = count($steps);

        return response()->json([
            'success' => true,
            'data'    => [
                'steps'     => $steps,
                'progress'  => round((count($completed) / $total) * 100),
                'completed' => count($completed) === $total,
                'next_step' => $this->getNextStep($steps),
            ],
        ]);
    }

    /**
     * Guide l'utilisateur vers sa première action (écriture de capital, compte banque).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuickStart(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        if (!$tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun tenant associé.',
            ], 400);
        }

        $suggestions = [];

        // Suggérer l'écriture de capital
        if (!JournalEntry::where('tenant_id', $tenantId)->exists()) {
            $capitalAccount = ChartAccount::where('tenant_id', $tenantId)
                ->where('code', 'like', '101%')
                ->first();

            $bankAccount = ChartAccount::where('tenant_id', $tenantId)
                ->where('code', 'like', '511%')
                ->first();

            if ($capitalAccount && $bankAccount) {
                $suggestions[] = [
                    'type'        => 'capital_entry',
                    'title'       => 'Enregistrer votre apport en capital',
                    'description' => 'Créez votre première écriture pour enregistrer l\'apport initial dans l\'entreprise.',
                    'action_url'  => '/comptabilite/ecritures',
                    'action_label'=> 'Créer l\'écriture',
                    'template'    => [
                        'journal_code' => 'OD',
                        'description'  => 'Apport en capital social',
                        'lines'        => [
                            ['account_code' => $bankAccount->code, 'debit' => 0, 'credit' => 0, 'label' => 'Banque (à remplir)'],
                            ['account_code' => $capitalAccount->code, 'debit' => 0, 'credit' => 0, 'label' => 'Capital (à remplir)'],
                        ],
                    ],
                ];
            }
        }

        // Suggérer la création d'un compte bancaire
        if (!BankAccount::where('tenant_id', $tenantId)->exists()) {
            $suggestions[] = [
                'type'         => 'bank_account',
                'title'        => 'Ajouter votre compte bancaire',
                'description'  => 'Lie votre compte bancaire réel au plan comptable pour suivre vos transactions.',
                'action_url'   => '/banque/comptes',
                'action_label' => 'Ajouter un compte',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $suggestions,
        ]);
    }

    /**
     * Retourne la première étape non complétée du parcours d'onboarding.
     *
     * @param  array  $steps  Tableau associatif des étapes (nom => complétée ou non)
     * @return string|null
     */
    private function getNextStep(array $steps): ?string
    {
        foreach ($steps as $step => $completed) {
            if (!$completed) {
                return $step;
            }
        }
        return null;
    }
}
