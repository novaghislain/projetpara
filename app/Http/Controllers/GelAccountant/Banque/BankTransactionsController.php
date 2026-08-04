<?php

namespace App\Http\Controllers\GelAccountant\Banque;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankTransactionsController extends Controller
{
    /**
     * Affiche le registre des transactions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $comptes = BankAccount::where('client_id', $clientId)->get();
        $selectedAccountId = $request->query('account_id');

        $query = BankTransaction::where('client_id', $clientId)
            ->with(['bankAccount', 'invoice']);

        if ($selectedAccountId) {
            $query->where('bank_account_id', $selectedAccountId);
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderByDesc('transaction_date')
                              ->orderByDesc('id')
                              ->paginate(50);

        return view('gel-accountant.banque.transactions.index', compact('comptes', 'transactions', 'selectedAccountId'));
    }

    /**
     * Enregistre manuellement une transaction (encaissement ou décaissement).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
        ]);

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($validated['bank_account_id']);

        $debit = $validated['type'] === 'debit' ? $validated['amount'] : 0;
        $credit = $validated['type'] === 'credit' ? $validated['amount'] : 0;

        // Calcul du nouveau solde
        $newBalance = $compte->current_balance + $credit - $debit;

        $transaction = BankTransaction::create([
            'client_id' => $clientId,
            'bank_account_id' => $compte->id,
            'transaction_date' => $validated['transaction_date'],
            'value_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $newBalance,
            'reference' => $validated['reference'],
            'category' => $validated['category'],
            'status' => 'cleared',
            'is_reconciled' => false,
            'is_imported' => false,
        ]);

        // Mise à jour du solde du compte
        $compte->update(['current_balance' => $newBalance]);

        return redirect()->back()->with('success', 'Transaction enregistrée avec succès.');
    }

    /**
     * Importe un relevé bancaire au format CSV.
     */
    public function import(Request $request)
    {
        $user = Auth::user();
        $clientId = $user->active_client_id ?? $user->client_id;

        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $compte = BankAccount::where('client_id', $clientId)->findOrFail($request->bank_account_id);
        $file = $request->file('file');

        $count = 0;
        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ";"); // Support pour séparateur point-virgule (courant en francophonie)
            if (count($header) == 1) {
                // Essayer la virgule si le point-virgule ne donne qu'une colonne
                rewind($handle);
                $header = fgetcsv($handle, 1000, ",");
            }

            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (count($data) == 1 && count($header) > 1) {
                    // Essayer la virgule
                    $data = str_getcsv($data[0], ",");
                }

                // Format attendu: Date, Description, Debit, Credit
                if (count($data) >= 4) {
                    $date = \Carbon\Carbon::parse(trim($data[0]))->format('Y-m-d');
                    $desc = trim($data[1]);
                    $debit = (float) str_replace([' ', ','], ['', '.'], trim($data[2]));
                    $credit = (float) str_replace([' ', ','], ['', '.'], trim($data[3]));

                    if ($debit > 0 || $credit > 0) {
                        $newBalance = $compte->current_balance + $credit - $debit;

                        BankTransaction::create([
                            'client_id' => $clientId,
                            'bank_account_id' => $compte->id,
                            'transaction_date' => $date,
                            'description' => $desc,
                            'debit' => $debit,
                            'credit' => $credit,
                            'balance' => $newBalance,
                            'status' => 'cleared',
                            'is_reconciled' => false,
                            'is_imported' => true,
                        ]);

                        $compte->update(['current_balance' => $newBalance]);
                        $count++;
                    }
                }
            }
            fclose($handle);
        }

        return redirect()->back()->with('success', "{$count} transactions ont été importées avec succès.");
    }
}
