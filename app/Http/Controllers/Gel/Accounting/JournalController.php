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
     * Contrôleur de gestion des écritures comptables (journal).
     * Permet de créer des écritures avec validation d'équilibre
     * débit/crédit, de les valider, et de les supprimer.
     */

    /**
     * Page formulaire de création d'écriture comptable.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\View\View
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
     * API : Liste des journaux pour un client.
     *
     * @param int $clientId L'identifiant du client
     * @return \Illuminate\Http\JsonResponse La liste des journaux
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
     * API : Détail d'un journal avec ses lignes.
     *
     * @param int $clientId L'identifiant du client
     * @param int $id L'identifiant du journal
     * @return \Illuminate\Http\JsonResponse Le journal avec ses lignes
     */
    public function getJournal($clientId, $id)
    {
        $journal = AccountingJournal::where('client_id', $clientId)
            ->with(['lines.account', 'createdBy:id,name'])
            ->findOrFail($id);

        return response()->json($journal);
    }

    /**
     * API : Crée une écriture comptable avec validation débit/crédit.
     *
     * @param Request $request La requête HTTP avec les données de l'écriture
     * @return \Illuminate\Http\JsonResponse Le journal créé
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

        // Validation de l'équilibre : total débits = total crédits
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json([
                'message' => 'Le montant total des débits (' . number_format($totalDebit, 2) . ') doit être égal au total des crédits (' . number_format($totalCredit, 2) . ')'
            ], 422);
        }

        // Création du journal et de ses lignes dans une transaction
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
     * API : Valide (poste) une écriture comptable.
     *
     * @param Request $request La requête HTTP (avec client_id optionnel)
     * @param int $id L'identifiant du journal
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function post(Request $request, $id)
    {
        $journal = AccountingJournal::findOrFail($id);

        // Vérification que le client_id correspond si fourni
        if ($request->filled('client_id') && (int) $journal->client_id !== (int) $request->input('client_id')) {
            abort(403, 'Accès non autorisé à cette écriture.');
        }

        // Une écriture déjà postée ne peut pas l'être à nouveau
        if ($journal->status === 'posted') {
            return response()->json(['message' => 'Cette écriture est déjà validée'], 409);
        }

        // Vérification finale de l'équilibre avant validation
        if (!$journal->is_balanced) {
            return response()->json(['message' => 'L\'écriture n\'est pas équilibrée'], 422);
        }

        $journal->update(['status' => 'posted']);

        return response()->json(['message' => 'Écriture validée avec succès']);
    }

    /**
     * API : Supprime une écriture comptable (brouillon seulement).
     *
     * @param Request $request La requête HTTP (avec client_id optionnel)
     * @param int $id L'identifiant du journal
     * @return \Illuminate\Http\JsonResponse Message de confirmation
     */
    public function destroy(Request $request, $id)
    {
        $journal = AccountingJournal::findOrFail($id);

        // Vérification que le client_id correspond si fourni
        if ($request->filled('client_id') && (int) $journal->client_id !== (int) $request->input('client_id')) {
            abort(403, 'Accès non autorisé à cette écriture.');
        }

        // Une écriture validée ne peut pas être supprimée
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
