<?php

namespace App\Http\Controllers\Gel\Accounting;

use App\Models\AccountingJournal;
use App\Models\AccountingJournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalController extends BaseGelAccountingController
{
    /**
     * Page formulaire de création d'écriture.
     */
    public function create($clientId)
    {
        return view('app', [
            'page' => 'gel-accounting-journal-form',
            'clientId' => $clientId,
        ]);
    }

    // ─── API ────────────────────────────────────────────────────

    /**
     * API: Liste des journaux pour un client.
     */
    public function listAll($clientId)
    {
        $journals = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy:id,name'])
            ->latest()
            ->get();

        return response()->json($journals);
    }

    /**
     * API: Détail d'un journal.
     */
    public function getJournal($clientId, $id)
    {
        $journal = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy:id,name'])
            ->findOrFail($id);

        return response()->json($journal);
    }

    /**
     * API: Créer une écriture comptable.
     */
    public function store(Request $request)
    {
        $clientId = $this->getClientId($request);
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
     * API: Valider (poster) une écriture comptable.
     */
    public function post(Request $request, $id)
    {
        $journal = AccountingJournal::findOrFail($id);

        // Vérifier que le client_id correspond si fourni dans la requête
        if ($request->filled('client_id') && (int) $journal->client_id !== (int) $request->input('client_id')) {
            abort(403, 'Accès non autorisé à cette écriture.');
        }

        if ($journal->status === 'posted') {
            return response()->json(['message' => 'Cette écriture est déjà validée'], 409);
        }

        // Vérifier l'équilibre
        if (!$journal->is_balanced) {
            return response()->json(['message' => 'L\'écriture n\'est pas équilibrée'], 422);
        }

        $journal->update(['status' => 'posted']);

        return response()->json(['message' => 'Écriture validée avec succès']);
    }

    /**
     * API: Supprimer une écriture comptable.
     */
    public function destroy(Request $request, $id)
    {
        $journal = AccountingJournal::findOrFail($id);

        // Vérifier que le client_id correspond si fourni dans la requête
        if ($request->filled('client_id') && (int) $journal->client_id !== (int) $request->input('client_id')) {
            abort(403, 'Accès non autorisé à cette écriture.');
        }

        if ($journal->status === 'posted') {
            return response()->json(['message' => 'Impossible de supprimer une écriture validée'], 409);
        }

        DB::transaction(function () use ($journal) {
            $journal->lines()->delete();
            $journal->delete();
        });

        return response()->json(['message' => 'Écriture supprimée']);
    }
}
