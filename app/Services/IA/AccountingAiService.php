<?php

namespace App\Services\IA;

use App\Models\AccountingAccount;
use App\Models\Client;
use App\Models\JournalEntry;
use App\Models\EntryLine;
use App\Models\AiSuggestion;
use App\Models\AiLearningLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingAiService
{
    /**
     * Analyse et catégorise une transaction non classifiée
     * Retourne une suggestion de compte SYSCOHADA avec score de confiance
     */
    public function categorizeTransaction(string $libelle, float $montant, string $type = 'charge'): array
    {
        $libelle = strtolower(trim($libelle));
        $suggestions = [];

        // --- RÈGLES DE CATÉGORISATION BASÉES SUR LE LIBELLÉ ---

        // Achats / Fournisseurs
        if (str_contains($libelle, 'fournisseur') || str_contains($libelle, 'achat')) {
            $suggestions[] = [
                'account_code' => '401',
                'account_name' => 'Fournisseurs',
                'confidence' => 85,
                'reason' => 'Libellé mentionne un fournisseur ou achat',
                'type' => 'credit',
            ];
        }

        // Salaire / Paie
        if (str_contains($libelle, 'salaire') || str_contains($libelle, 'paie') ||
            str_contains($libelle, 'paye') || str_contains($libelle, 'rémunération') || str_contains($libelle, 'remuneration')) {
            $suggestions[] = [
                'account_code' => '661',
                'account_name' => 'Rémunérations du personnel',
                'confidence' => 90,
                'reason' => 'Libellé correspond à une charge de personnel',
                'type' => 'debit',
            ];
            $suggestions[] = [
                'account_code' => '421',
                'account_name' => 'Personnel - Rémunérations dues',
                'confidence' => 85,
                'reason' => 'Contrepartie : dette sociale',
                'type' => 'credit',
            ];
        }

        // Loyer
        if (str_contains($libelle, 'loyer') || str_contains($libelle, 'bail') ||
            str_contains($libelle, 'location')) {
            $suggestions[] = [
                'account_code' => '613',
                'account_name' => 'Locations et charges locatives',
                'confidence' => 92,
                'reason' => 'Libellé correspond à un loyer',
                'type' => 'debit',
            ];
        }

        // Banque / Frais bancaires
        if (str_contains($libelle, 'banque') || str_contains($libelle, 'frais bancaire') ||
            str_contains($libelle, 'commission') || str_contains($libelle, 'agios')) {
            $suggestions[] = [
                'account_code' => '631',
                'account_name' => 'Frais bancaires',
                'confidence' => 88,
                'reason' => 'Libellé mentionne des frais bancaires',
                'type' => 'debit',
            ];
        }

        // Électricité / Eau / Énergie
        if (str_contains($libelle, 'électricité') || str_contains($libelle, 'electricite') ||
            str_contains($libelle, 'eau') || str_contains($libelle, 'sbee') ||
            str_contains($libelle, 'cebet') || str_contains($libelle, 'énergie') || str_contains($libelle, 'energie')) {
            $suggestions[] = [
                'account_code' => '612',
                'account_name' => 'Énergie et eau',
                'confidence' => 93,
                'reason' => 'Libellé correspond à une facture d\'énergie/eau',
                'type' => 'debit',
            ];
        }

        // Télécommunications
        if (str_contains($libelle, 'téléphone') || str_contains($libelle, 'telephone') ||
            str_contains($libelle, 'internet') || str_contains($libelle, 'mobile money') ||
            str_contains($libelle, 'momo') || str_contains($libelle, 'abonnement')) {
            $suggestions[] = [
                'account_code' => '614',
                'account_name' => 'Télécommunications',
                'confidence' => 87,
                'reason' => 'Libellé correspond à des frais de télécom',
                'type' => 'debit',
            ];
        }

        // Client / Vente
        if (str_contains($libelle, 'client') || str_contains($libelle, 'vente') ||
            str_contains($libelle, 'facture') || str_contains($libelle, 'prestation')) {
            $suggestions[] = [
                'account_code' => '411',
                'account_name' => 'Clients',
                'confidence' => 86,
                'reason' => 'Libellé mentionne un client ou une vente',
                'type' => 'debit',
            ];
            $suggestions[] = [
                'account_code' => '701',
                'account_name' => 'Ventes de marchandises',
                'confidence' => 80,
                'reason' => 'Contrepartie : produit de vente',
                'type' => 'credit',
            ];
        }

        // Impôts et taxes
        if (str_contains($libelle, 'impôt') || str_contains($libelle, 'impot') ||
            str_contains($libelle, 'taxe') || str_contains($libelle, 'tva') ||
            str_contains($libelle, 'dgi') || str_contains($libelle, 'fisc')) {
            $suggestions[] = [
                'account_code' => '635',
                'account_name' => 'Impôts et taxes',
                'confidence' => 89,
                'reason' => 'Libellé correspond à un impôt ou taxe',
                'type' => 'debit',
            ];
        }

        // CNSS / Charges sociales
        if (str_contains($libelle, 'cnss') || str_contains($libelle, 'sécurité sociale') ||
            str_contains($libelle, 'securite sociale') || str_contains($libelle, 'cotisation sociale')) {
            $suggestions[] = [
                'account_code' => '664',
                'account_name' => 'Charges de sécurité sociale',
                'confidence' => 91,
                'reason' => 'Libellé correspond à une cotisation CNSS',
                'type' => 'debit',
            ];
        }

        // Si aucune règle n'a matché, suggestion générique basée sur le type
        if (empty($suggestions)) {
            if ($type === 'charge') {
                $suggestions[] = [
                    'account_code' => '601',
                    'account_name' => 'Achats de marchandises',
                    'confidence' => 40,
                    'reason' => 'Aucune correspondance précise - suggestion générique',
                    'type' => 'debit',
                ];
            } elseif ($type === 'produit') {
                $suggestions[] = [
                    'account_code' => '701',
                    'account_name' => 'Ventes de marchandises',
                    'confidence' => 40,
                    'reason' => 'Aucune correspondance précise - suggestion générique',
                    'type' => 'credit',
                ];
            } else {
                $suggestions[] = [
                    'account_code' => '471',
                    'account_name' => 'Comptes d\'attente',
                    'confidence' => 30,
                    'reason' => 'Transaction non classifiable - mise en attente',
                    'type' => 'debit',
                ];
            }
        }

        // Trier par score de confiance décroissant
        usort($suggestions, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $suggestions;
    }

    /**
     * Détecte les anomalies comptables pour un client sur une période donnée
     */
    public function detectAnomalies(int $clientId, ?string $periodeDebut = null, ?string $periodeFin = null): array
    {
        $anomalies = [];

        // 1. Écritures non équilibrées
        $unbalanced = JournalEntry::where('client_id', $clientId)
            ->where(function ($q) {
                $q->where('is_balanced', false)
                  ->orWhereRaw('ABS(total_debit - total_credit) > 0.01');
            })
            ->when($periodeDebut, fn($q) => $q->whereDate('entry_date', '>=', $periodeDebut))
            ->when($periodeFin, fn($q) => $q->whereDate('entry_date', '<=', $periodeFin))
            ->get();

        foreach ($unbalanced as $entry) {
            $diff = ($entry->total_debit ?? 0) - ($entry->total_credit ?? 0);
            $anomalies[] = [
                'type' => 'unbalanced_entry',
                'severity' => 'critical',
                'title' => 'Écriture non équilibrée',
                'message' => "L'écriture #{$entry->reference} du {$entry->entry_date->format('d/m/Y')} présente un écart de " . number_format(abs($diff), 0, ',', ' ') . " FCFA.",
                'entry_id' => $entry->id,
                'diff' => $diff,
                'suggested_action' => 'Vérifier et compléter les lignes de l\'écriture pour équilibrer le montant.',
            ];
        }

        // 2. Transactions en attente depuis plus de 30 jours
        $pendingOld = JournalEntry::where('client_id', $clientId)
            ->whereIn('status', ['brouillon', 'draft'])
            ->whereDate('created_at', '<=', now()->subDays(30))
            ->count();

        if ($pendingOld > 0) {
            $anomalies[] = [
                'type' => 'pending_transactions',
                'severity' => 'high',
                'title' => 'Transactions en attente',
                'message' => "{$pendingOld} écriture(s) sont en statut brouillon depuis plus de 30 jours.",
                'suggested_action' => 'Finaliser ou supprimer ces écritures en attente.',
            ];
        }

        // 3. Doublons potentiels (même montant, même date, même description)
        $duplicates = DB::table('journal_entries as j1')
            ->join('journal_entries as j2', function ($join) {
                $join->on('j1.client_id', '=', 'j2.client_id')
                    ->on('j1.total_debit', '=', 'j2.total_debit')
                    ->on('j1.total_credit', '=', 'j2.total_credit')
                    ->on('j1.entry_date', '=', 'j2.entry_date')
                    ->on('j1.id', '<', 'j2.id');
            })
            ->where('j1.client_id', $clientId)
            ->where('j1.description', '!=', '')
            ->where(DB::raw('TRIM(j1.description)'), '=', DB::raw('TRIM(j2.description)'))
            ->when($periodeDebut, fn($q) => $q->whereDate('j1.entry_date', '>=', $periodeDebut))
            ->when($periodeFin, fn($q) => $q->whereDate('j1.entry_date', '<=', $periodeFin))
            ->limit(10)
            ->get(['j1.id as id1', 'j1.reference as ref1', 'j1.entry_date', 'j1.total_debit', 'j1.total_credit', 'j2.id as id2', 'j2.reference as ref2']);

        foreach ($duplicates as $dup) {
            $montant = max($dup->total_debit, $dup->total_credit);
            $anomalies[] = [
                'type' => 'duplicate',
                'severity' => 'high',
                'title' => 'Doublon potentiel',
                'message' => "Deux écritures au même montant (" . number_format($montant, 0, ',', ' ') . " FCFA) à la même date : #{$dup->ref1} et #{$dup->ref2}.",
                'suggested_action' => 'Vérifier s\'il s\'agit d\'un doublon et supprimer l\'écriture en trop.',
            ];
        }

        // 4. Compte de trésorerie négatif
        $negativeCash = AccountingAccount::where('client_id', $clientId)
            ->where('code', 'like', '5%')
            ->where('is_active', true)
            ->get()
            ->filter(function ($account) {
                $solde = EntryLine::where('account_id', $account->id)
                    ->select(DB::raw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance'))
                    ->value('balance');
                return $solde < 0;
            });

        foreach ($negativeCash as $account) {
            $anomalies[] = [
                'type' => 'negative_cash',
                'severity' => 'critical',
                'title' => 'Compte de trésorerie négatif',
                'message' => "Le compte {$account->code} - {$account->name} présente un solde négatif.",
                'account_code' => $account->code,
                'suggested_action' => 'Vérifier les écritures et reclasser si nécessaire.',
            ];
        }

        // 5. Écart balance (somme débits ≠ somme crédits)
        $totals = EntryLine::join('journal_entries', 'entry_lines.entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.client_id', $clientId)
            ->when($periodeDebut, fn($q) => $q->whereDate('journal_entries.entry_date', '>=', $periodeDebut))
            ->when($periodeFin, fn($q) => $q->whereDate('journal_entries.entry_date', '<=', $periodeFin))
            ->select(
                DB::raw('COALESCE(SUM(entry_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(entry_lines.credit), 0) as total_credit')
            )
            ->first();

        if ($totals && abs($totals->total_debit - $totals->total_credit) > 1) {
            $anomalies[] = [
                'type' => 'balance_imbalance',
                'severity' => 'critical',
                'title' => 'Balance non équilibrée',
                'message' => 'La somme des débits (' . number_format($totals->total_debit, 0, ',', ' ') . ' FCFA) ne correspond pas à la somme des crédits (' . number_format($totals->total_credit, 0, ',', ' ') . ' FCFA).',
                'suggested_action' => 'Lancer une vérification complète de la balance.',
            ];
        }

        return $anomalies;
    }

    /**
     * Génère des suggestions d'écritures de régularisation de fin de période
     */
    public function suggestRegularizations(int $clientId, string $dateFin): array
    {
        $suggestions = [];

        // 1. Amortissements linéaires (comptes 28)
        $immobilisations = AccountingAccount::where('client_id', $clientId)
            ->where('code', 'like', '2%')
            ->where('is_active', true)
            ->get();

        foreach ($immobilisations as $immob) {
            $suggestions[] = [
                'type' => 'regularization',
                'agent' => 'ohada',
                'priority' => 'normal',
                'title' => 'Amortissement - ' . $immob->name,
                'message' => "Suggestion d'écriture d'amortissement pour le compte {$immob->code} ({$immob->name}).",
                'data' => [
                    'debit_account' => '681',
                    'debit_label' => 'Dotations aux amortissements',
                    'credit_account' => $immob->code,
                    'credit_label' => $immob->name,
                    'suggested_amount' => 0,
                ],
                'confidence' => 75,
                'action_type' => 'create_regularization',
            ];
        }

        // 2. Charges constatées d'avance (compte 481)
        $suggestions[] = [
            'type' => 'regularization',
            'agent' => 'ohada',
            'priority' => 'normal',
            'title' => 'Charges constatées d\'avance',
            'message' => 'Vérifier s\'il existe des charges payées d\'avance à régulariser (abonnements, assurances, loyers).',
            'data' => [
                'debit_account' => '481',
                'debit_label' => 'Charges constatées d\'avance',
                'credit_account' => '6',
                'credit_label' => 'Compte de charge concerné',
                'suggested_amount' => 0,
            ],
            'confidence' => 60,
            'action_type' => 'create_regularization',
        ];

        // 3. Produits à recevoir (compte 418)
        $suggestions[] = [
            'type' => 'regularization',
            'agent' => 'ohada',
            'priority' => 'normal',
            'title' => 'Produits à recevoir',
            'message' => 'Vérifier les prestations facturées mais non encore comptabilisées en fin de période.',
            'data' => [
                'debit_account' => '418',
                'debit_label' => 'Clients - Produits à recevoir',
                'credit_account' => '7',
                'credit_label' => 'Compte de produit concerné',
                'suggested_amount' => 0,
            ],
            'confidence' => 50,
            'action_type' => 'create_regularization',
        ];

        return $suggestions;
    }

    /**
     * Enregistre une suggestion dans la base (ai_suggestions)
     */
    public function createSuggestion(int $clientId, int $createdBy, array $data): AiSuggestion
    {
        return AiSuggestion::create([
            'client_id' => $clientId,
            'user_id' => $createdBy,
            'agent' => 'ohada',
            'type' => $data['type'] ?? 'suggestion',
            'title' => $data['title'],
            'description' => $data['message'] ?? $data['title'],
            'data' => $data['data'] ?? null,
            'metadata' => [
                'priority' => $data['priority'] ?? 'normal',
                'confidence' => $data['confidence'] ?? 50,
                'action_type' => $data['action_type'] ?? null,
                'action_payload' => $data['action_payload'] ?? null,
            ],
            'status' => 'pending',
        ]);
    }

    /**
     * Traite le retour humain pour l'apprentissage continu
     */
    public function logLearning(
        string $action,
        array $inputData,
        array $suggestedOutput,
        ?array $actualOutput,
        ?bool $wasCorrect,
        ?int $clientId = null,
        ?int $userId = null
    ): void {
        AiLearningLog::create([
            'agent' => 'ohada',
            'type' => $action,
            'input_data' => $inputData,
            'output_data' => $suggestedOutput,
            'correction' => $actualOutput,
            'metadata' => ['was_correct' => $wasCorrect],
            'client_id' => $clientId,
            'user_id' => $userId,
        ]);

        Log::info('AI Learning Log (OHADA) : ' . $action, [
            'was_correct' => $wasCorrect,
            'client_id' => $clientId,
        ]);
    }

    /**
     * Exécute une action approuvée : crée une écriture comptable à partir d'une suggestion
     */
    public function executeApprovedAction(AiSuggestion $suggestion, int $userId): bool
    {
        $metadata = $suggestion->metadata;
        $payload = $metadata['action_payload'] ?? null;

        if (!$payload || ($metadata['action_type'] ?? null) !== 'categorize_transaction') {
            return false;
        }

        try {
            DB::beginTransaction();

            // Créer l'écriture comptable
            $entry = JournalEntry::create([
                'client_id' => $suggestion->client_id,
                'entry_date' => $payload['date'] ?? now(),
                'reference' => $payload['reference'] ?? 'AI-' . uniqid(),
                'description' => $payload['description'] ?? 'Catégorisation automatique AI',
                'total_debit' => collect($payload['lines'])->sum('debit'),
                'total_credit' => collect($payload['lines'])->sum('credit'),
                'is_balanced' => true,
                'status' => 'valide',
                'created_by' => $userId,
            ]);

            // Créer les lignes d'écriture
            foreach ($payload['lines'] as $line) {
                EntryLine::create([
                    'client_id' => $suggestion->client_id,
                    'entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'account_code' => $line['account_code'] ?? null,
                    'account_label' => $line['account_label'] ?? '',
                    'description' => $line['description'] ?? $entry->description,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            // Marquer la suggestion comme approuvée
            $suggestion->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
            ]);

            // Enregistrer l'apprentissage
            $this->logLearning(
                'categorize',
                ['libelle' => $payload['description'] ?? ''],
                $payload['lines'],
                $payload['lines'],
                true,
                $suggestion->client_id,
                $userId
            );

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur exécution suggestion AI : ' . $e->getMessage());
            return false;
        }
    }
}
