<?php

namespace App\Http\Controllers\GelAccountant\Facturation;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur pour la gestion des paiements reçus (Recevoir un paiement).
 */
class PaymentsController extends Controller
{
    /**
     * Liste des paiements.
     */
    public function index(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $payments = Payment::where('client_id', $clientId)
            ->where('type', 'incoming')
            ->with(['partner', 'invoice'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('gel-accountant.payments.index', compact('payments'));
    }

    /**
     * Formulaire pour recevoir un paiement.
     */
    public function create(Request $request)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        // Si on vient d'une facture spécifique
        $selectedInvoiceId = $request->query('invoice_id');
        $selectedInvoice = null;

        if ($selectedInvoiceId) {
            $selectedInvoice = Invoice::where('client_id', $clientId)
                ->where('id', $selectedInvoiceId)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->first();
        }

        // Liste des clients ayant des factures impayées
        $partners = Partner::where('client_id', $clientId)
            ->whereIn('type', ['customer', 'both'])
            ->whereHas('invoices', function($q) {
                $q->where('type', 'customer_invoice')
                  ->whereNotIn('status', ['paid', 'cancelled', 'draft']);
            })
            ->orderBy('company_name')
            ->get();

        // Récupérer les factures impayées pour le client sélectionné, ou tous si aucun
        $invoicesQuery = Invoice::where('client_id', $clientId)
            ->where('type', 'customer_invoice')
            ->whereNotIn('status', ['paid', 'cancelled', 'draft']);
            
        if ($selectedInvoice) {
            $invoicesQuery->where('partner_id', $selectedInvoice->partner_id);
        }
        
        $unpaidInvoices = $invoicesQuery->orderBy('due_date')->get();

        return view('gel-accountant.payments.create', compact('partners', 'unpaidInvoices', 'selectedInvoice'));
    }

    /**
     * Enregistre le paiement reçu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:partners,id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference' => 'nullable|string',
            'amount_received' => 'required|numeric|min:0.01',
            'invoices' => 'required|array',
            'invoices.*.id' => 'required|exists:invoices,id',
            'invoices.*.amount_applied' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

                $clientId = session('active_client_id') ?? session('current_client_id');
        $partner = Partner::findOrFail($validated['partner_id']);

        // Vérifier que le total appliqué correspond au montant reçu
        $totalApplied = collect($validated['invoices'])->sum('amount_applied');
        if (abs($totalApplied - $validated['amount_received']) > 0.1) {
            return back()->withInput()->withErrors(['amount_received' => 'Le montant reçu doit être égal au total des montants appliqués aux factures.']);
        }

        return DB::transaction(function () use ($validated, $clientId, $partner) {
            
            // Générer le numéro de paiement
            $lastPayment = Payment::where('client_id', $clientId)
                ->where('type', 'incoming')
                ->orderByDesc('id')
                ->first();

            $nextNum = $lastPayment && $lastPayment->payment_number
                ? intval(preg_replace('/[^0-9]/', '', substr($lastPayment->payment_number, -4))) + 1
                : 1;

            $paymentNumber = 'PAI-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Pour chaque facture où un montant est appliqué
            foreach ($validated['invoices'] as $invData) {
                if ($invData['amount_applied'] > 0) {
                    $invoice = Invoice::where('client_id', $clientId)->findOrFail($invData['id']);
                    
                    // Créer le paiement
                    Payment::create([
                        'client_id' => $clientId,
                        'type' => 'incoming',
                        'payment_number' => $paymentNumber,
                        'partner_id' => $partner->id,
                        'invoice_id' => $invoice->id,
                        'payment_date' => $validated['payment_date'],
                        'payment_method' => $validated['payment_method'],
                        'amount' => $invData['amount_applied'],
                        'reference' => $validated['reference'],
                        'status' => 'completed',
                        'notes' => $validated['notes'],
                    ]);

                    // Mettre à jour la facture
                    $invoice->paid_amount += $invData['amount_applied'];
                    $invoice->balance_due = max(0, $invoice->total - $invoice->paid_amount);
                    
                    if ($invoice->balance_due <= 0) {
                        $invoice->status = 'paid';
                    } elseif ($invoice->status === 'sent' || $invoice->status === 'overdue') {
                        $invoice->status = 'partially_paid';
                    }
                    
                    $invoice->save();
                }
            }

            return redirect()->route('gel-accountant.factures.index')
                ->with('success', "Paiement {$paymentNumber} enregistré avec succès !");
        });
    }

    /**
     * API pour récupérer les factures impayées d'un client (pour AJAX).
     */
    public function getUnpaidInvoices(Request $request, $partnerId)
    {
                $clientId = session('active_client_id') ?? session('current_client_id');

        $invoices = Invoice::where('client_id', $clientId)
            ->where('partner_id', $partnerId)
            ->where('type', 'customer_invoice')
            ->whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->orderBy('due_date')
            ->get(['id', 'invoice_number', 'due_date', 'total', 'balance_due']);

        return response()->json($invoices);
    }
}
