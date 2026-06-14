<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    /**
     * Page de la comptabilité (rendue via company.blade.php).
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return view('company', [
            'page' => 'company-accounting',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Vérifie que l'utilisateur authentifié a bien accès à ce client.
     */
    private function getClientId(): int
    {
        $user = Auth::user();
        if (!$user->client_id) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $user->client_id;
    }

    // ─── PLAN COMPTABLE ─────────────────────────────────────────────

    /**
     * API: Liste des comptes comptables.
     */
    public function accounts()
    {
        $clientId = $this->getClientId();
        $accounts = AccountingAccount::where('client_id', $clientId)
            ->withCount('journalLines')
            ->orderBy('code')
            ->get();
        return response()->json($accounts);
    }

    /**
     * API: Créer un compte comptable.
     */
    public function storeAccount(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:actif,passif,charge,produit,tresorerie',
            'is_active' => 'boolean',
        ]);

        // Vérifier unicité du code pour ce client
        $exists = AccountingAccount::where('client_id', $clientId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Un compte avec ce code existe déjà pour ce client'], 409);
        }

        $account = AccountingAccount::create([
            'client_id' => $clientId,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($account, 201);
    }

    /**
     * API: Mettre à jour un compte comptable.
     */
    public function updateAccount(Request $request, $id)
    {
        $clientId = $this->getClientId();
        $account = AccountingAccount::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:actif,passif,charge,produit,tresorerie',
            'is_active' => 'boolean',
        ]);

        // Vérifier unicité (sauf pour ce compte)
        $exists = AccountingAccount::where('client_id', $clientId)
            ->where('code', $validated['code'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Un compte avec ce code existe déjà pour ce client'], 409);
        }

        $account->update($validated);

        return response()->json($account);
    }

    /**
     * API: Supprimer un compte comptable.
     */
    public function deleteAccount($id)
    {
        $clientId = $this->getClientId();
        $account = AccountingAccount::where('client_id', $clientId)->findOrFail($id);

        // Empêcher suppression si des écritures existent
        if ($account->journalLines()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce compte car des écritures y sont liées'
            ], 409);
        }

        $account->delete();

        return response()->json(['message' => 'Compte supprimé']);
    }

    // ─── JOURNAUX ──────────────────────────────────────────────────

    /**
     * API: Liste des journaux / écritures.
     */
    public function journals()
    {
        $clientId = $this->getClientId();
        $journals = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy:id,name'])
            ->latest()
            ->get();

        return response()->json($journals);
    }

    /**
     * API: Créer une écriture comptable.
     */
    public function storeJournal(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'journal_type' => 'required|string|in:recette,depense,banque,od,achat,vente',
            'entry_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'required|string|max:1000',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:accounting_accounts,id',
            'lines.*.label' => 'required|string|max:500',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        // Valider l'équilibre débit/crédit
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'message' => 'Le montant total des débits (' . number_format($totalDebit, 2) . ') doit être égal au total des crédits (' . number_format($totalCredit, 2) . ')'
            ], 422);
        }

        $journal = DB::transaction(function () use ($validated, $clientId) {
            $journal = AccountingJournal::create([
                'client_id' => $clientId,
                'journal_type' => $validated['journal_type'],
                'entry_date' => $validated['entry_date'],
                'reference' => $validated['reference'],
                'description' => $validated['description'],
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['lines'] as $line) {
                AccountingJournalLine::create([
                    'journal_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'label' => $line['label'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            return $journal;
        });

        return response()->json($journal->load('lines.account'), 201);
    }

    /**
     * API: Détail d'un journal.
     */
    public function getJournal($id)
    {
        $clientId = $this->getClientId();
        $journal = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy:id,name'])
            ->findOrFail($id);

        return response()->json($journal);
    }

    /**
     * API: Valider (poster) une écriture comptable.
     */
    public function postJournal($id)
    {
        $clientId = $this->getClientId();
        $journal = AccountingJournal::where('client_id', $clientId)->findOrFail($id);

        if ($journal->status === 'posted') {
            return response()->json(['message' => 'Cette écriture est déjà validée'], 409);
        }

        // Vérifier l'équilibre
        if (!$journal->is_balanced) {
            return response()->json(['message' => "L'écriture n'est pas équilibrée"], 422);
        }

        $journal->update(['status' => 'posted']);

        return response()->json(['message' => 'Écriture validée avec succès']);
    }

    /**
     * API: Supprimer une écriture comptable.
     */
    public function deleteJournal($id)
    {
        $clientId = $this->getClientId();
        $journal = AccountingJournal::where('client_id', $clientId)->findOrFail($id);

        if ($journal->status === 'posted') {
            return response()->json(['message' => 'Impossible de supprimer une écriture validée'], 409);
        }

        DB::transaction(function () use ($journal) {
            $journal->lines()->delete();
            $journal->delete();
        });

        return response()->json(['message' => 'Écriture supprimée']);
    }

    // ─── RAPPORTS ──────────────────────────────────────────────────

    /**
     * API: Balance des comptes.
     */
    public function balance()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get();

        $balance = $accounts->map(function ($account) {
            $debitTotal = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->sum('debit');

            $creditTotal = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->sum('credit');

            $balance = $debitTotal - $creditTotal;

            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'debit' => $debitTotal,
                'credit' => $creditTotal,
                'balance' => $balance,
                'balance_abs' => abs($balance),
                'sens' => $balance >= 0 ? 'Débiteur' : 'Créditeur',
            ];
        });

        return response()->json([
            'accounts' => $balance,
            'totals' => [
                'debit' => $balance->sum('debit'),
                'credit' => $balance->sum('credit'),
            ],
        ]);
    }

    /**
     * API: Grand Livre.
     */
    public function grandLivre()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get();

        $data = $accounts->map(function ($account) {
            $lines = $account->journalLines()
                ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                ->with('journal:id,entry_date,reference,description')
                ->orderBy('created_at')
                ->get();

            $runningBalance = 0;
            $linesWithBalance = $lines->map(function ($line) use (&$runningBalance) {
                $runningBalance += $line->debit - $line->credit;
                return [
                    'date' => $line->journal->entry_date->format('Y-m-d'),
                    'reference' => $line->journal->reference,
                    'label' => $line->label,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'balance' => $runningBalance,
                ];
            });

            return [
                'account' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                ],
                'lines' => $linesWithBalance,
                'total_debit' => $lines->sum('debit'),
                'total_credit' => $lines->sum('credit'),
                'balance' => $runningBalance,
            ];
        });

        return response()->json($data);
    }

    /**
     * API: Bilan comptable.
     */
    public function bilan()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->get();

        $actif = $accounts->filter(fn($a) => in_array($a->type, ['actif', 'tresorerie']))->values();
        $passif = $accounts->filter(fn($a) => $a->type === 'passif')->values();

        $build = function ($items) {
            return $items->map(function ($account) {
                $balance = $account->journalLines()
                    ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                    ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
                    ->value('balance');

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => abs($balance),
                ];
            });
        };

        return response()->json([
            'actif' => $build($actif),
            'passif' => $build($passif),
            'total_actif' => $build($actif)->sum('balance'),
            'total_passif' => $build($passif)->sum('balance'),
        ]);
    }

    /**
     * API: Compte de résultat.
     */
    public function resultat()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->get();

        $charges = $accounts->filter(fn($a) => $a->type === 'charge')->values();
        $produits = $accounts->filter(fn($a) => $a->type === 'produit')->values();

        $build = function ($items) {
            return $items->map(function ($account) {
                $balance = $account->journalLines()
                    ->whereHas('journal', fn($q) => $q->where('status', 'posted'))
                    ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
                    ->value('balance');

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'montant' => abs($balance),
                ];
            });
        };

        $totalCharges = $build($charges)->sum('montant');
        $totalProduits = $build($produits)->sum('montant');

        return response()->json([
            'charges' => $build($charges),
            'produits' => $build($produits),
            'total_charges' => $totalCharges,
            'total_produits' => $totalProduits,
            'resultat' => $totalProduits - $totalCharges,
        ]);
    }

    /**
     * API: Statistiques pour le tableau de bord de la comptabilité.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $totalAccounts = AccountingAccount::where('client_id', $clientId)->count();
        $activeAccounts = AccountingAccount::where('client_id', $clientId)->active()->count();
        $totalJournals = AccountingJournal::where('client_id', $clientId)->count();
        $postedJournals = AccountingJournal::where('client_id', $clientId)->where('status', 'posted')->count();
        $draftJournals = AccountingJournal::where('client_id', $clientId)->where('status', 'draft')->count();

        $totalDebit = AccountingJournalLine::whereHas('journal', fn($q) => $q->where('client_id', $clientId))
            ->sum('debit');
        $totalCredit = AccountingJournalLine::whereHas('journal', fn($q) => $q->where('client_id', $clientId))
            ->sum('credit');

        // Dernières écritures
        $recentJournals = AccountingJournal::where('client_id', $clientId)
            ->with('createdBy:id,name')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'total_accounts' => $totalAccounts,
            'active_accounts' => $activeAccounts,
            'total_journals' => $totalJournals,
            'posted_journals' => $postedJournals,
            'draft_journals' => $draftJournals,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'recent_journals' => $recentJournals,
        ]);
    }
}
