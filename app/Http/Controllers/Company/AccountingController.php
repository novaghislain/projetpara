<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AccountingAccount;
use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use App\Models\AccountingJournalSequence;
use App\Models\FiscalYear;
use App\Services\AuditTrailService;
use App\Services\TenantDomainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountingController extends BaseCompanyController
{
    /**
     * Affiche la page de comptabilité.
     */
    public function index()
    {
        $clientId = $this->getClientId();

        // Partager les modules comptables actifs pour la sidebar dynamique
        $modules = TenantDomainService::getActiveModules($clientId);
        $sidebar = TenantDomainService::getModulesSidebar($clientId);
        view()->share('accountingModulesJson', json_encode($modules));
        view()->share('accountingSidebarJson', json_encode($sidebar));

        return view('company', ['page' => 'company-accounting', 'clientId' => $clientId]);
    }

    // ─── Plan comptable ─────────────────────────────────────────────

    /**
     * API: Liste tous les comptes du client (plats).
     */
    public function accounts()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                return [
                    'id'             => $acc->id,
                    'code'           => $acc->code,
                    'name'           => $acc->name,
                    'type'           => $acc->type,
                    'syscohada_class'=> $acc->syscohada_class,
                    'parent_id'      => $acc->parent_id,
                    'is_syscohada'   => $acc->is_syscohada,
                    'has_tva'        => $acc->has_tva,
                    'tva_rate'       => (float) $acc->tva_rate,
                    'typeLabel'      => $this->accountTypeLabel($acc->syscohada_class ?: $acc->type),
                    'is_active'      => $acc->is_active,
                    'debit'          => (float) $acc->debit_total,
                    'credit'         => (float) $acc->credit_total,
                    'balance'        => (float) $acc->balance,
                ];
            });

        return response()->json($accounts);
    }

    /**
     * API: Arborescence des comptes.
     */
    public function accountsTree()
    {
        $clientId = $this->getClientId();

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->orderBy('code')
            ->get();

        $tree = $accounts->whereNull('parent_id')->values()->map(function ($root) use ($accounts) {
            return $this->buildNode($root, $accounts);
        });

        return response()->json($tree);
    }

    private function buildNode($node, $all)
    {
        $children = $all->where('parent_id', $node->id)->values()->map(function ($child) use ($all) {
            return $this->buildNode($child, $all);
        });

        return [
            'id'             => $node->id,
            'code'           => $node->code,
            'name'           => $node->name,
            'type'           => $node->type,
            'syscohada_class'=> $node->syscohada_class,
            'is_active'      => $node->is_active,
            'debit'          => (float) $node->debit_total,
            'credit'         => (float) $node->credit_total,
            'balance'        => (float) $node->balance,
            'children'       => $children,
        ];
    }

    /**
     * API: Crée un compte comptable.
     */
    public function storeAccount(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'code'            => 'required|string|max:20',
            'name'            => 'required|string|max:255',
            'type'            => 'required|string|max:10',
            'syscohada_class' => 'nullable|string|size:1',
            'parent_id'       => 'nullable|exists:accounting_accounts,id',
            'is_syscohada'    => 'nullable|boolean',
            'has_tva'         => 'nullable|boolean',
            'tva_rate'        => 'nullable|numeric|min:0|max:100',
            'is_active'       => 'boolean',
        ]);

        $exists = AccountingAccount::where('client_id', $clientId)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ce code compte existe déjà.'], 422);
        }

        $account = AccountingAccount::create([
            'client_id'       => $clientId,
            'code'            => $validated['code'],
            'name'            => $validated['name'],
            'type'            => $validated['type'],
            'syscohada_class' => $validated['syscohada_class'] ?? $validated['type'],
            'parent_id'       => $validated['parent_id'] ?? null,
            'is_syscohada'    => $validated['is_syscohada'] ?? false,
            'has_tva'         => $validated['has_tva'] ?? false,
            'tva_rate'        => $validated['tva_rate'] ?? null,
            'is_active'       => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'message' => 'Compte créé avec succès.',
            'account' => $account,
        ], 201);
    }

    /**
     * API: Modifie un compte comptable.
     */
    public function updateAccount(Request $request, $id)
    {
        $clientId = $this->getClientId();

        $account = AccountingAccount::where('client_id', $clientId)->findOrFail($id);

        $validated = $request->validate([
            'code'            => 'sometimes|string|max:20',
            'name'            => 'sometimes|string|max:255',
            'type'            => 'sometimes|string|max:10',
            'syscohada_class' => 'nullable|string|size:1',
            'parent_id'       => 'nullable|exists:accounting_accounts,id',
            'is_syscohada'    => 'nullable|boolean',
            'has_tva'         => 'nullable|boolean',
            'tva_rate'        => 'nullable|numeric|min:0|max:100',
            'is_active'       => 'sometimes|boolean',
        ]);

        if (isset($validated['code']) && $validated['code'] !== $account->code) {
            $exists = AccountingAccount::where('client_id', $clientId)
                ->where('code', $validated['code'])
                ->where('id', '!=', $id)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Ce code compte est déjà utilisé.'], 422);
            }
        }

        $account->update($validated);

        return response()->json([
            'message' => 'Compte mis à jour.',
            'account' => $account->fresh(),
        ]);
    }

    /**
     * API: Supprime un compte comptable.
     */
    public function deleteAccount($id)
    {
        $clientId = $this->getClientId();
        $account = AccountingAccount::where('client_id', $clientId)->findOrFail($id);

        if ($account->journalLines()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce compte : des écritures y sont liées.',
            ], 422);
        }

        $account->delete();
        return response()->json(['message' => 'Compte supprimé.']);
    }

    /**
     * API: Import CSV du plan comptable SYSCOHADA.
     */
    public function importAccounts(Request $request)
    {
        $clientId = $this->getClientId();

        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);
        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                $code = trim($data['code'] ?? '');
                $name = trim($data['name'] ?? '');
                $type = trim($data['type'] ?? $data['syscohada_class'] ?? substr($code, 0, 1));

                if (empty($code) || empty($name)) {
                    continue;
                }

                $exists = AccountingAccount::where('client_id', $clientId)
                    ->where('code', $code)
                    ->exists();

                if (!$exists) {
                    AccountingAccount::create([
                        'client_id'       => $clientId,
                        'code'            => $code,
                        'name'            => $name,
                        'type'            => $type,
                        'syscohada_class' => $type,
                        'is_syscohada'    => true,
                        'is_active'       => true,
                    ]);
                    $imported++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur d\'import: ' . $e->getMessage()], 422);
        } finally {
            fclose($handle);
        }

        return response()->json([
            'message' => "{$imported} comptes importés avec succès.",
            'imported' => $imported,
        ]);
    }

    // ─── Journaux ──────────────────────────────────────────────────

    /**
     * API: Liste les journaux.
     */
    public function journals()
    {
        $clientId = $this->getClientId();

        $journals = AccountingJournal::where('client_id', $clientId)
            ->withCount('lines')
            ->latest()
            ->get()
            ->map(function ($j) {
                return [
                    'id'            => $j->id,
                    'journal_type'  => $j->journal_type,
                    'reference'     => $j->reference,
                    'numero_piece'  => $j->numero_piece,
                    'description'   => $j->description,
                    'entry_date'    => $j->entry_date?->format('Y-m-d'),
                    'status'        => $j->status,
                    'created_by'    => $j->createdBy?->name,
                    'lines_count'   => $j->lines_count,
                    'debit_total'   => (float) $j->debit_total,
                    'credit_total'  => (float) $j->credit_total,
                    'is_balanced'   => $j->is_balanced,
                    'is_reversal'   => $j->is_reversal,
                    'fiscal_year_id'=> $j->fiscal_year_id,
                    'validated_at'  => $j->validated_at?->format('d/m/Y H:i'),
                    'created_at'    => $j->created_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json($journals);
    }

    /**
     * API: Crée un journal avec ses lignes.
     */
    public function storeJournal(Request $request)
    {
        $clientId = $this->getClientId();
        $user = Auth::user();

        $validated = $request->validate([
            'journal_type'        => 'required|in:achat,vente,banque,caisse,operations_diverses,od,salaire,investissement,anouveaux,paie,hav',
            'entry_date'          => 'required|date',
            'reference'           => 'nullable|string|max:255',
            'description'         => 'nullable|string',
            'fiscal_year_id'      => 'nullable|exists:fiscal_years,id',
            'lines'               => 'required|array|min:2',
            'lines.*.account_id'  => 'required|exists:accounting_accounts,id',
            'lines.*.label'       => 'required|string|max:255',
            'lines.*.debit'       => 'required_without:lines.*.credit|numeric|min:0',
            'lines.*.credit'      => 'required_without:lines.*.debit|numeric|min:0',
            'lines.*.tva_code'    => 'nullable|string|max:20',
            'lines.*.tva_rate'    => 'nullable|numeric|min:0|max:100',
            'lines.*.tva_amount'  => 'nullable|numeric|min:0',
            'lines.*.tva_type'    => 'nullable|in:collected,deductible',
            'lines.*.aib_rate'    => 'nullable|numeric|min:0|max:100',
            'lines.*.aib_amount'  => 'nullable|numeric|min:0',
            'lines.*.due_date'    => 'nullable|date',
            'lines.*.cost_center_id' => 'nullable|integer',
        ]);

        $journal = DB::transaction(function () use ($validated, $clientId, $user) {
            $reference = $validated['reference'] ?? 'JNL-' . now()->format('YmdHis');

            // Assign fiscal year if not provided
            $fiscalYearId = $validated['fiscal_year_id'];
            if (!$fiscalYearId) {
                $fy = FiscalYear::where('client_id', $clientId)
                    ->where('status', 'open')
                    ->where('date_start', '<=', $validated['entry_date'])
                    ->where('date_end', '>=', $validated['entry_date'])
                    ->first();
                $fiscalYearId = $fy?->id;
            }

            // Vérifier que toutes les lignes appartiennent au client
            $accountIds = collect($validated['lines'])->pluck('account_id')->unique();
            $validAccounts = AccountingAccount::where('client_id', $clientId)
                ->whereIn('id', $accountIds)
                ->pluck('id');

            foreach ($accountIds as $accId) {
                if (!$validAccounts->contains($accId)) {
                    abort(422, 'Le compte #' . $accId . ' ne vous appartient pas.');
                }
            }

            $journal = AccountingJournal::create([
                'client_id'      => $clientId,
                'journal_type'   => $validated['journal_type'],
                'entry_date'     => $validated['entry_date'],
                'reference'      => $reference,
                'description'    => $validated['description'] ?? null,
                'status'         => 'draft',
                'created_by'     => $user->id,
                'fiscal_year_id' => $fiscalYearId,
            ]);

            $linesData = [];
            foreach ($validated['lines'] as $line) {
                $linesData[] = new AccountingJournalLine([
                    'account_id'    => $line['account_id'],
                    'label'         => $line['label'],
                    'debit'         => $line['debit'] ?? 0,
                    'credit'        => $line['credit'] ?? 0,
                    'tva_code'      => $line['tva_code'] ?? null,
                    'tva_rate'      => $line['tva_rate'] ?? null,
                    'tva_amount'    => $line['tva_amount'] ?? null,
                    'tva_type'      => $line['tva_type'] ?? null,
                    'aib_rate'      => $line['aib_rate'] ?? null,
                    'aib_amount'    => $line['aib_amount'] ?? null,
                    'due_date'      => $line['due_date'] ?? null,
                    'cost_center_id'=> $line['cost_center_id'] ?? null,
                ]);
            }

            $journal->lines()->saveMany($linesData);

            return $journal->fresh(['lines', 'lines.account']);
        });

        return response()->json([
            'message' => 'Écriture comptable créée.',
            'journal' => $journal,
        ], 201);
    }

    /**
     * API: Affiche un journal avec ses lignes.
     */
    public function getJournal($id)
    {
        $clientId = $this->getClientId();

        $journal = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy'])
            ->findOrFail($id);

        return response()->json([
            'journal' => [
                'id'            => $journal->id,
                'journal_type'  => $journal->journal_type,
                'reference'     => $journal->reference,
                'numero_piece'  => $journal->numero_piece,
                'description'   => $journal->description,
                'entry_date'    => $journal->entry_date?->format('Y-m-d'),
                'status'        => $journal->status,
                'created_by'    => $journal->createdBy?->name,
                'fiscal_year_id'=> $journal->fiscal_year_id,
                'is_reversal'   => $journal->is_reversal,
                'debit_total'   => (float) $journal->debit_total,
                'credit_total'  => (float) $journal->credit_total,
                'is_balanced'   => $journal->is_balanced,
                'lines'         => $journal->lines->map(function ($l) {
                    return [
                        'id'           => $l->id,
                        'account_id'   => $l->account_id,
                        'account_code' => $l->account?->code,
                        'account_name' => $l->account?->name,
                        'label'        => $l->label,
                        'debit'        => (float) $l->debit,
                        'credit'       => (float) $l->credit,
                        'tva_code'     => $l->tva_code,
                        'tva_rate'     => (float) $l->tva_rate,
                        'tva_amount'   => (float) $l->tva_amount,
                        'tva_type'     => $l->tva_type,
                        'aib_rate'     => (float) $l->aib_rate,
                        'aib_amount'   => (float) $l->aib_amount,
                        'due_date'     => $l->due_date?->format('Y-m-d'),
                    ];
                }),
                'created_at'    => $journal->created_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * API: Poste un journal (draft → posted).
     */
    public function postJournal($id)
    {
        $clientId = $this->getClientId();

        $journal = AccountingJournal::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        if (!$journal->is_balanced) {
            return response()->json([
                'message' => 'Le journal n\'est pas équilibré (total débit ≠ total crédit).',
            ], 422);
        }

        DB::transaction(function () use ($journal) {
            $journal->update([
                'status'       => 'posted',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            // Auto-assign numero_piece si vide
            if (empty($journal->numero_piece)) {
                $seq = AccountingJournalSequence::getNextNumber(
                    $journal->client_id,
                    $journal->journal_type,
                    $journal->fiscal_year_id ?? 0
                );
                $journal->update(['numero_piece' => $seq]);
            }

            AuditTrailService::log($journal, 'posted', null, $journal->toArray(), 'Écriture comptable postée');
        });

        return response()->json([
            'message' => 'Écriture comptable postée.',
            'journal' => $journal->fresh(['lines', 'lines.account']),
        ]);
    }

    /**
     * API: Extourne un journal posté.
     */
    public function reverseJournal($id)
    {
        $clientId = $this->getClientId();
        $user = Auth::user();

        $original = AccountingJournal::where('client_id', $clientId)
            ->where('status', 'posted')
            ->with('lines')
            ->findOrFail($id);

        if ($original->is_reversal) {
            return response()->json(['message' => 'Impossible d\'extourner une écriture d\'extourne.'], 422);
        }

        $reversal = DB::transaction(function () use ($original, $clientId, $user) {
            $reversal = AccountingJournal::create([
                'client_id'         => $clientId,
                'journal_type'      => $original->journal_type,
                'entry_date'        => now()->format('Y-m-d'),
                'reference'         => 'EXT-' . $original->reference,
                'description'       => 'Extourne de: ' . $original->reference . ' - ' . ($original->description ?? ''),
                'status'            => 'posted',
                'created_by'        => $user->id,
                'fiscal_year_id'    => $original->fiscal_year_id,
                'is_reversal'       => true,
                'reversed_journal_id' => $original->id,
                'validated_by'      => $user->id,
                'validated_at'      => now(),
            ]);

            // Inverse débit/credit
            $linesData = [];
            foreach ($original->lines as $line) {
                $linesData[] = new AccountingJournalLine([
                    'account_id' => $line->account_id,
                    'label'      => 'Extourne: ' . $line->label,
                    'debit'      => $line->credit,  // swap
                    'credit'     => $line->debit,    // swap
                ]);
            }
            $reversal->lines()->saveMany($linesData);

            return $reversal->fresh(['lines', 'lines.account']);
        });

        return response()->json([
            'message' => 'Extourne créée avec succès.',
            'journal' => $reversal,
        ]);
    }

    /**
     * API: Supprime un journal en brouillon.
     */
    public function deleteJournal($id)
    {
        $clientId = $this->getClientId();

        $journal = AccountingJournal::where('client_id', $clientId)
            ->where('status', 'draft')
            ->findOrFail($id);

        $journal->lines()->delete();
        $journal->delete();

        return response()->json(['message' => 'Brouillon supprimé.']);
    }

    /**
     * API: Liste les types de journaux disponibles.
     */
    public function journalTypes()
    {
        return response()->json([
            ['type' => 'achat', 'label' => 'Achats', 'prefix' => 'HA'],
            ['type' => 'vente', 'label' => 'Ventes', 'prefix' => 'VT'],
            ['type' => 'banque', 'label' => 'Banque', 'prefix' => 'BQ'],
            ['type' => 'caisse', 'label' => 'Caisse', 'prefix' => 'CA'],
            ['type' => 'operations_diverses', 'label' => 'Opérations Diverses', 'prefix' => 'OD'],
            ['type' => 'od', 'label' => 'OD (A nouveau)', 'prefix' => 'AN'],
            ['type' => 'salaire', 'label' => 'Salaires', 'prefix' => 'PA'],
            ['type' => 'investissement', 'label' => 'Investissements', 'prefix' => 'IN'],
            ['type' => 'paie', 'label' => 'Paie', 'prefix' => 'PA'],
            ['type' => 'hav', 'label' => 'Hav', 'prefix' => 'HA'],
            ['type' => 'anouveaux', 'label' => 'À Nouveaux', 'prefix' => 'AN'],
        ]);
    }

    // ─── Rapports ──────────────────────────────────────────────────

    /**
     * API: Balance des comptes (4 colonnes).
     */
    public function balance(Request $request)
    {
        $clientId = $this->getClientId();
        $fiscalYearId = $request->input('fiscal_year_id');

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->with('journalLines')
            ->get()
            ->map(function ($acc) use ($fiscalYearId) {
                $linesQuery = $acc->journalLines();
                if ($fiscalYearId) {
                    $linesQuery->whereHas('journal', function ($q) use ($fiscalYearId) {
                        $q->where('fiscal_year_id', $fiscalYearId);
                    });
                }
                $debit  = (float) $linesQuery->sum('debit');
                $credit = (float) $linesQuery->sum('credit');
                return [
                    'code'    => $acc->code,
                    'name'    => $acc->name,
                    'syscohada_class' => $acc->syscohada_class,
                    'type'    => $acc->type,
                    'debit'   => $debit,
                    'credit'  => $credit,
                    'solde'   => round($debit - $credit, 2),
                ];
            });

        $totals = [
            'total_debit'  => round($accounts->sum('debit'), 2),
            'total_credit' => round($accounts->sum('credit'), 2),
        ];

        // 8-column balance: aggregate by SYSCOHADA class
        $byClass = $accounts->groupBy('syscohada_class')->map(function ($group, $class) {
            return [
                'class'  => $class,
                'label'  => $this->accountTypeLabel($class),
                'debit'  => round($group->sum('debit'), 2),
                'credit' => round($group->sum('credit'), 2),
                'solde'  => round($group->sum('solde'), 2),
                'count'  => $group->count(),
            ];
        })->values();

        return response()->json([
            'accounts' => $accounts,
            'totals'   => $totals,
            'by_class' => $byClass,
        ]);
    }

    /**
     * API: Grand livre.
     */
    public function grandLivre(Request $request)
    {
        $clientId = $this->getClientId();

        $query = AccountingJournalLine::whereHas('journal', function ($q) use ($clientId) {
            $q->where('client_id', $clientId)->where('status', 'posted');
        })->with(['account', 'journal']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('date_from')) {
            $query->whereHas('journal', function ($q) use ($request) {
                $q->where('entry_date', '>=', $request->date_from);
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('journal', function ($q) use ($request) {
                $q->where('entry_date', '<=', $request->date_to);
            });
        }

        if ($request->filled('journal_type')) {
            $query->whereHas('journal', function ($q) use ($request) {
                $q->where('journal_type', $request->journal_type);
            });
        }

        if ($request->filled('fiscal_year_id')) {
            $query->whereHas('journal', function ($q) use ($request) {
                $q->where('fiscal_year_id', $request->fiscal_year_id);
            });
        }

        $lines = $query->orderBy('journal_id')->orderBy('id')->get()->map(function ($l) {
            return [
                'id'            => $l->id,
                'date'          => $l->journal?->entry_date?->format('Y-m-d'),
                'reference'     => $l->journal?->reference,
                'numero_piece'  => $l->journal?->numero_piece,
                'journal_type'  => $l->journal?->journal_type,
                'account_code'  => $l->account?->code,
                'account_name'  => $l->account?->name,
                'label'         => $l->label,
                'debit'         => (float) $l->debit,
                'credit'        => (float) $l->credit,
            ];
        });

        // Solde cumulé
        $runningBalance = 0;
        $lines = $lines->map(function ($l) use (&$runningBalance) {
            $runningBalance += $l['debit'] - $l['credit'];
            $l['solde_cumule'] = round($runningBalance, 2);
            return $l;
        });

        $totals = [
            'total_debit'  => round($lines->sum('debit'), 2),
            'total_credit' => round($lines->sum('credit'), 2),
        ];

        $accounts = AccountingAccount::where('client_id', $clientId)
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return response()->json([
            'lines'    => $lines,
            'totals'   => $totals,
            'accounts' => $accounts,
        ]);
    }

    /**
     * API: Bilan SYSCOHADA.
     */
    public function bilan(Request $request)
    {
        $clientId = $this->getClientId();
        $fiscalYearId = $request->input('fiscal_year_id');

        $query = AccountingAccount::where('client_id', $clientId)->active();

        $accounts = $query->with('journalLines')->orderBy('code')->get()->map(function ($acc) use ($fiscalYearId) {
            $linesQuery = $acc->journalLines();
            if ($fiscalYearId) {
                $linesQuery->whereHas('journal', function ($q) use ($fiscalYearId) {
                    $q->where('fiscal_year_id', $fiscalYearId);
                });
            }
            $solde = (float) ($linesQuery->sum('debit') - $linesQuery->sum('credit'));
            return [
                'code'  => $acc->code,
                'name'  => $acc->name,
                'class' => $acc->syscohada_class ?: $acc->type,
                'solde' => $solde,
            ];
        });

        // SYSCOHADA: Actif = classes 2,3,4,5 / Passif = classe 1
        $actif  = $accounts->filter(fn ($a) => in_array($a['class'], ['2', '3', '4', '5']));
        $passif = $accounts->filter(fn ($a) => $a['class'] === '1');

        return response()->json([
            'actif'  => [
                'accounts' => $actif->values(),
                'total'    => round($actif->sum('solde'), 2),
            ],
            'passif' => [
                'accounts' => $passif->values(),
                'total'    => round($passif->sum('solde'), 2),
            ],
        ]);
    }

    /**
     * API: Compte de résultat SYSCOHADA.
     */
    public function resultat(Request $request)
    {
        $clientId = $this->getClientId();
        $fiscalYearId = $request->input('fiscal_year_id');

        $query = AccountingAccount::where('client_id', $clientId)->active();

        $accounts = $query->with('journalLines')->orderBy('code')->get()->map(function ($acc) use ($fiscalYearId) {
            $linesQuery = $acc->journalLines();
            if ($fiscalYearId) {
                $linesQuery->whereHas('journal', function ($q) use ($fiscalYearId) {
                    $q->where('fiscal_year_id', $fiscalYearId);
                });
            }
            $solde = (float) ($linesQuery->sum('debit') - $linesQuery->sum('credit'));
            return [
                'code'  => $acc->code,
                'name'  => $acc->name,
                'class' => $acc->syscohada_class ?: $acc->type,
                'solde' => $solde,
            ];
        });

        $charges  = $accounts->filter(fn ($a) => $a['class'] === '6');
        $produits = $accounts->filter(fn ($a) => $a['class'] === '7');

        $totalCharges  = round($charges->sum('solde'), 2);
        $totalProduits = round($produits->sum('solde'), 2);
        $resultat      = round($totalProduits - $totalCharges, 2);

        return response()->json([
            'charges'  => [
                'accounts' => $charges->values(),
                'total'    => $totalCharges,
            ],
            'produits' => [
                'accounts' => $produits->values(),
                'total'    => $totalProduits,
            ],
            'resultat' => $resultat,
        ]);
    }

    /**
     * API: Statistiques comptables.
     */
    public function stats()
    {
        $clientId = $this->getClientId();

        $journals = AccountingJournal::where('client_id', $clientId)->get();
        $accounts = AccountingAccount::where('client_id', $clientId)->get();
        $client = \App\Models\Client::find($clientId);

        $stats = [
            'total_accounts'  => $accounts->count(),
            'active_accounts' => $accounts->where('is_active', true)->count(),
            'total_journals'  => $journals->count(),
            'posted_journals' => $journals->where('status', 'posted')->count(),
            'draft_journals'  => $journals->where('status', 'draft')->count(),
            'total_debit'     => (float) $journals->sum(fn ($j) => $j->debit_total),
            'total_credit'    => (float) $journals->sum(fn ($j) => $j->credit_total),
            'by_type'         => $journals->groupBy('journal_type')->map(function ($group) {
                return [
                    'count'  => $group->count(),
                    'debit'  => (float) $group->sum(fn ($j) => $j->debit_total),
                    'credit' => (float) $group->sum(fn ($j) => $j->credit_total),
                ];
            }),
            'domaine' => $client?->domain_code,
            'domain_kpis' => $client ? $this->getDomainKpis($client) : [],
        ];

        return response()->json($stats);
    }

    /**
     * Retourne les KPIs adaptés au domaine d'activité du client.
     */
    private function getDomainKpis(\App\Models\Client $client): array
    {
        $clientId = $client->id;
        $domainCode = $client->domain_code;

        // Soldes des comptes de résultat via journaux
        $compteSolde = function ($prefix) use ($clientId) {
            $accounts = AccountingAccount::where('client_id', $clientId)
                ->where('code', 'like', $prefix . '%')
                ->get();
            return (float) $accounts->reduce(function ($carry, $a) {
                return $carry + ((float) $a->debit_total) - ((float) $a->credit_total);
            }, 0);
        };

        $soldeCompte = function ($code) use ($clientId) {
            $account = AccountingAccount::where('client_id', $clientId)
                ->where('code', $code)->first();
            if (!$account) return 0;
            return (float) $account->balance;
        };

        // Soldes trésorerie (521 + 571)
        $tresorerie = $compteSolde('5');
        if ($tresorerie == 0) $tresorerie = ($soldeCompte('521') ?: 0) + ($soldeCompte('571') ?: 0);

        // Compte clients (411)
        $creances = $soldeCompte('411') ?: $compteSolde('41');

        // Compte fournisseurs (401)
        $dettes = $soldeCompte('401') ?: $compteSolde('40');

        switch ($domainCode) {
            case 'commerce':
                $ca = $compteSolde('701') ?: $compteSolde('70');
                $achats = $compteSolde('601') ?: $compteSolde('60');
                return [
                    'ca_mensuel'          => round($ca, 2),
                    'achats_mensuel'      => round($achats, 2),
                    'marge_brute'         => round($ca - $achats, 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'creances_clients'    => round($creances, 2),
                    'dettes_fournisseurs' => round($dettes, 2),
                    'stock_valorise'      => round($compteSolde('3'), 2),
                    'tva_a_payer'         => round($soldeCompte('441'), 2),
                ];

            case 'hotel':
                $recettes = $compteSolde('706');
                return [
                    'recettes_nuitees'    => round($recettes, 2),
                    'taux_occupation'     => null, // nécessite données chambres
                    'revpar'              => null,
                    'taxe_nuitee_due'     => round($soldeCompte('447'), 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'creances_clients'    => round($creances, 2),
                    'tva_a_payer'         => round($soldeCompte('441'), 2),
                ];

            case 'scolaire':
                $recettes = $compteSolde('706');
                return [
                    'recettes_scolarite'  => round($recettes, 2),
                    'frais_percus'        => round($recettes, 2),
                    'impayes'             => round($creances, 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'charges_totales'     => round($compteSolde('6'), 2),
                    'tva_a_payer'         => round($soldeCompte('441'), 2),
                ];

            case 'location':
                $loyers = $compteSolde('703');
                return [
                    'loyers_du_mois'      => round($loyers, 2),
                    'loyers_encaisses'     => round($loyers - $creances, 2),
                    'taux_recouvrement'   => $loyers > 0 ? round(($loyers - $creances) / $loyers * 100, 1) : null,
                    'impayes_total'       => round($creances, 2),
                    'tresorerie'          => round($tresorerie, 2),
                ];

            case 'tontine':
                $cotisations = $compteSolde('70');
                return [
                    'cotisations_mois'    => round($cotisations, 2),
                    'total_cagnotte'      => round($cotisations, 2),
                    'tresorerie'          => round($tresorerie, 2),
                ];

            case 'transport':
                $ca = $compteSolde('706');
                $carburant = $compteSolde('605') ?: $compteSolde('606');
                return [
                    'ca_fret_mois'        => round($ca, 2),
                    'charges_carburant'   => round($carburant, 2),
                    'marge_transport'     => round($ca - $carburant, 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'creances'            => round($creances, 2),
                ];

            case 'cabinet_comptable':
                $honoraires = $compteSolde('706');
                return [
                    'honoraires_mois'     => round($honoraires, 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'creances_clients'    => round($creances, 2),
                ];

            default:
                // KPIs génériques pour les autres domaines
                return [
                    'ca_total'            => round($compteSolde('7'), 2),
                    'charges_total'       => round($compteSolde('6'), 2),
                    'resultat'            => round($compteSolde('7') - $compteSolde('6'), 2),
                    'tresorerie'          => round($tresorerie, 2),
                    'creances_clients'    => round($creances, 2),
                ];
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // ─── Stock Métier — API ─────────────────────────────────────
    // ═══════════════════════════════════════════════════════════════

    /**
     * API: Liste les articles en stock du client.
     */
    public function stockItems(Request $request)
    {
        $clientId = $this->getClientId();

        $items = \App\Models\ErpItem::where('client_id', $clientId)
            ->with('stockMovements')
            ->orderBy('designation')
            ->get()
            ->map(function ($item) {
                $in  = $item->stockMovements->where('type','entry')->sum('quantity');
                $out = $item->stockMovements->where('type','exit')->sum('quantity');
                return [
                    'id'             => $item->id,
                    'reference'      => $item->reference,
                    'designation'    => $item->designation,
                    'stock'          => $in - $out,
                    'stock_alert'    => $item->stock_alert,
                    'purchase_price' => (float) $item->purchase_price,
                    'selling_price'  => (float) $item->selling_price,
                    'unit'           => $item->unit,
                    'category'       => $item->category?->name,
                ];
            });

        return response()->json($items);
    }

    /**
     * API: Crée un nouvel article en stock.
     */
    public function storeStockItem(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'reference'      => 'required|string|max:50',
            'designation'    => 'required|string|max:255',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price'  => 'nullable|numeric|min:0',
            'stock_alert'    => 'nullable|integer|min:0',
            'unit'           => 'nullable|string|max:20',
        ]);

        $validated['client_id'] = $clientId;

        $item = \App\Models\ErpItem::create($validated);

        return response()->json(['message' => 'Article créé.', 'item' => $item], 201);
    }

    /**
     * API: Supprime un article en stock.
     */
    public function deleteStockItem($id)
    {
        $clientId = $this->getClientId();
        $item = \App\Models\ErpItem::where('client_id', $clientId)->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Article supprimé.']);
    }

    /**
     * API: Import CSV d'articles en stock.
     */
    public function importStockItems(Request $request)
    {
        $clientId = $this->getClientId();

        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv');
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle);
        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                $reference   = trim($data['reference'] ?? '');
                $designation = trim($data['designation'] ?? '');
                $purchase_price = (float) ($data['purchase_price'] ?? $data['prix_achat'] ?? 0);
                $selling_price  = (float) ($data['selling_price'] ?? $data['prix_vente'] ?? 0);
                $stock_alert    = (int) ($data['stock_alert'] ?? $data['seuil_alerte'] ?? 0);
                $unit           = trim($data['unit'] ?? $data['unite'] ?? 'unite');

                if (empty($reference) || empty($designation)) {
                    continue;
                }

                $exists = \App\Models\ErpItem::where('client_id', $clientId)
                    ->where('reference', $reference)
                    ->exists();

                if (!$exists) {
                    \App\Models\ErpItem::create([
                        'client_id'      => $clientId,
                        'reference'      => $reference,
                        'designation'    => $designation,
                        'purchase_price' => $purchase_price,
                        'selling_price'  => $selling_price,
                        'stock_alert'    => $stock_alert,
                        'unit'           => $unit,
                    ]);
                    $imported++;
                } else {
                    $errors[] = "Réf. '$reference' déjà existant, ignoré.";
                }
                if ($imported > 5000) break; // sécurité anti-dépassement
            }
            fclose($handle);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json(['message' => 'Erreur lors de l\'import : ' . $e->getMessage()], 422);
        }

        return response()->json([
            'message' => "{$imported} article(s) importé(s) avec succès.",
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /**
     * API: Mouvements de stock du client.
     */
    public function stockMovements(Request $request)
    {
        $clientId = $this->getClientId();

        $movements = \App\Models\ErpStockMovement::whereHas('item', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })
            ->with(['item', 'warehouse'])
            ->latest()
            ->take(200)
            ->get()
            ->map(function ($m) {
                return [
                    'id'           => $m->id,
                    'item'         => $m->item?->designation,
                    'item_ref'     => $m->item?->reference,
                    'type'         => $m->type,
                    'quantity'     => $m->quantity,
                    'warehouse'    => $m->warehouse?->name,
                    'motif'        => $m->motif,
                    'date'         => $m->movement_date?->format('Y-m-d'),
                    'created_at'   => $m->created_at?->format('Y-m-d H:i'),
                ];
            });

        return response()->json($movements);
    }

    /**
     * API: Enregistre un mouvement de stock (entrée/sortie).
     */
    public function storeStockMovement(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'erp_item_id'  => 'required|exists:erp_items,id',
            'type'         => 'required|in:entry,exit',
            'quantity'     => 'required|numeric|min:0.01',
            'motif'        => 'nullable|string|max:255',
            'movement_date'=> 'nullable|date',
        ]);

        // Vérifier que l'article appartient au client
        $item = \App\Models\ErpItem::where('client_id', $clientId)->findOrFail($validated['erp_item_id']);

        $movement = \App\Models\ErpStockMovement::create([
            'erp_item_id'   => $validated['erp_item_id'],
            'type'          => $validated['type'],
            'quantity'      => $validated['quantity'],
            'motif'         => $validated['motif'] ?? null,
            'movement_date' => $validated['movement_date'] ?? now(),
            'created_by'    => Auth::id(),
        ]);

        return response()->json(['message' => 'Mouvement enregistré.', 'movement' => $movement], 201);
    }

    // ─── Emballages consignés — API ────────────────────────────────

    /**
     * API: Liste les emballages du client.
     */
    public function emballages(Request $request)
    {
        $clientId = $this->getClientId();

        $items = \App\Models\AccountingEmballage::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->take(200)
            ->get()
            ->map(function ($e) {
                return [
                    'id'              => $e->id,
                    'type'            => $e->type,
                    'tiers_nom'       => $e->tiers_nom,
                    'tiers_type'      => $e->tiers_type,
                    'produit'         => $e->produit,
                    'quantite'        => $e->quantite,
                    'montant_consigne'=> (float) $e->montant_consigne,
                    'date_emission'   => $e->date_emission?->format('Y-m-d'),
                    'date_retour'     => $e->date_retour?->format('Y-m-d'),
                    'statut'          => $e->statut,
                    'notes'           => $e->notes,
                ];
            });

        return response()->json($items);
    }

    /**
     * API: Crée un emballage consigné.
     */
    public function storeEmballage(Request $request)
    {
        $clientId = $this->getClientId();

        $validated = $request->validate([
            'type'             => 'required|in:consigne,perdu,retourne,echange',
            'tiers_nom'        => 'nullable|string|max:255',
            'tiers_type'       => 'nullable|in:client,fournisseur',
            'produit'          => 'nullable|string|max:255',
            'quantite'         => 'required|integer|min:1',
            'montant_consigne' => 'nullable|numeric|min:0',
            'date_emission'    => 'nullable|date',
            'date_retour'      => 'nullable|date',
            'statut'           => 'nullable|in:en_cours,retourne,facture,perdu',
            'notes'            => 'nullable|string',
        ]);

        $validated['client_id'] = $clientId;
        $validated['created_by'] = Auth::id();
        $validated['statut'] ??= 'en_cours';

        $emballage = \App\Models\AccountingEmballage::create($validated);

        return response()->json(['message' => 'Emballage créé.', 'emballage' => $emballage], 201);
    }

    /**
     * API: Supprime un emballage.
     */
    public function deleteEmballage($id)
    {
        $clientId = $this->getClientId();
        $item = \App\Models\AccountingEmballage::where('client_id', $clientId)->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Emballage supprimé.']);
    }

    // ─── Helpers ───────────────────────────────────────────────────

    /**
     * Libellé du type de compte SYSCOHADA.
     */
    private function accountTypeLabel($type)
    {
        $labels = [
            '1' => 'Capitaux propres',
            '2' => 'Immobilisations',
            '3' => 'Stocks',
            '4' => 'Tiers',
            '5' => 'Financier',
            '6' => 'Charges',
            '7' => 'Produits',
            '8' => 'Comptes spéciaux',
            '9' => 'Comptes analytiques',
        ];
        return $labels[$type] ?? 'Classe ' . $type;
    }
}
