<?php

namespace App\Http\Controllers\GelSecretary\Communication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Gel\Task;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\RelanceMail;
use App\Services\AnthropicService;
use App\Services\AuditLogService;

class RelanceController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cabinetId = $user->cabinet_id;

        // Récupérer les IDs des clients rattachés au cabinet pour filtrer les factures
        $clientIds = Client::where('created_by', $user->id)
            ->pluck('id');

        // Factures échues : filtrer via client_id appartenant au cabinet
        $overdueInvoices = Invoice::with('client')
            ->whereIn('client_id', $clientIds)
            ->whereDate('due_date', '<', now()->toDateString())
            ->where('balance_due', '>', 0)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->get();

        // Tâches échues liées au cabinet
        $overdueTasks = Task::with('client')
            ->where('cabinet_id', $cabinetId)
            ->whereIn('statut', ['a_faire', 'en_cours'])
            ->whereNotNull('date_echeance')
            ->whereDate('date_echeance', '<', now()->toDateString())
            ->orderBy('date_echeance', 'asc')
            ->get();

        // Relances des devis (simulé via factures draft/sent > 7j)
        $pendingQuotes = Invoice::with('client')
            ->whereIn('client_id', $clientIds)
            ->whereIn('status', ['draft', 'sent'])
            ->where('created_at', '<', now()->subDays(7))
            ->get();
            
        // Contrats à relancer (< 30j)
        $expiringContracts = Client::whereIn('id', $clientIds)
            ->whereNotNull('contract_end')
            ->whereDate('contract_end', '<', now()->addDays(30))
            ->get();
            
        // Clients inactifs (> 6 mois)
        $inactiveClients = Client::whereIn('id', $clientIds)
            ->where('updated_at', '<', now()->subMonths(6))
            ->get();

        return view('gel-secretary.relances.index', [
            'overdueInvoices' => $overdueInvoices,
            'overdueTasks'    => $overdueTasks,
            'pendingQuotes' => $pendingQuotes,
            'expiringContracts' => $expiringContracts,
            'inactiveClients' => $inactiveClients,
            'currentSection'  => 'relances',
            'currentPage'     => 'relances',
        ]);
    }

    public function draft(Request $request)
    {
        $request->validate([
            'type' => 'required|in:invoice,task,quote,contract,inactive',
            'id' => 'required|integer'
        ]);
        
        $type = $request->type;
        $id = $request->id;
        
        $aiService = new AnthropicService();
        $prompt = "Génère un brouillon d'email de relance professionnel pour le client. Sois courtois, clair et va à l'essentiel.";
        
        if ($type === 'invoice') {
            $invoice = Invoice::with('client')->findOrFail($id);
            $clientName = $invoice->client ? $invoice->client->company_name : 'Client';
            $prompt .= " Contexte : Facture impayée n°{$invoice->invoice_number}. Client : {$clientName}. Montant restant dû : {$invoice->balance_due} euros. Date d'échéance : {$invoice->due_date}.";
        } elseif ($type === 'task') {
            $task = Task::with('client')->findOrFail($id);
            $clientName = $task->client ? $task->client->company_name : 'Client';
            $prompt .= " Contexte : Tâche/Demande en attente nommée '{$task->titre}'. Client concerné : {$clientName}. Date d'échéance : {$task->date_echeance}. On attend leur retour ou action.";
        } elseif ($type === 'quote') {
            $quote = Invoice::with('client')->findOrFail($id);
            $clientName = $quote->client ? $quote->client->company_name : 'Client';
            $prompt .= " Contexte : Devis/Proposition commerciale n°{$quote->invoice_number} envoyé(e) le {$quote->created_at->format('d/m/Y')}. Client concerné : {$clientName}. Sans réponse depuis plus de 7 jours. Demander s'ils ont des questions ou s'ils souhaitent valider la proposition.";
        } elseif ($type === 'contract') {
            $client = Client::findOrFail($id);
            $prompt .= " Contexte : Le contrat du client '{$client->company_name}' expire bientôt le {$client->contract_end}. Proposer un rendez-vous pour faire le point et renouveler le contrat.";
        } elseif ($type === 'inactive') {
            $client = Client::findOrFail($id);
            $prompt .= " Contexte : Le client '{$client->company_name}' n'a eu aucune activité avec nous depuis plus de 6 mois (dernière activité le {$client->updated_at->format('d/m/Y')}). Prendre des nouvelles, proposer un point téléphonique ou présenter nos nouveaux services.";
        }
        
        $system = "Tu es un assistant IA pour un secrétaire de direction. Rédige l'email. Ne mets pas d'objet, juste le corps. Laisse des balises [Nom] si besoin.";
        $draft = $aiService->generate($prompt, $system);
        
        AuditLogService::log('IA ACTION', auth()->user(), null, ['action' => "Génération brouillon relance ({$type} {$id})"]);
        
        return response()->json(['draft' => $draft]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'type' => 'required|in:invoice,task',
            'id'   => 'required|integer',
        ]);

        $type = $request->input('type');
        $id   = $request->input('id');

        $message = "Relance envoyée avec succès !";

        if ($type === 'invoice') {
            $invoice = Invoice::with('client')->findOrFail($id);
            $client  = $invoice->client;

            if ($client && $client->email) {
                Mail::to($client->email)->send(new RelanceMail([
                    'type'       => 'invoice',
                    'clientName' => $client->company_name,
                    'reference'  => $invoice->invoice_number,
                    'amount'     => number_format($invoice->balance_due, 2, ',', ' '),
                    'dueDate'    => \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y'),
                ]));
            }

            $message = "Relance envoyée pour la facture #{$invoice->invoice_number}.";

        } elseif ($type === 'task') {
            $task   = Task::with('client')->findOrFail($id);
            $client = $task->client;

            if ($client && $client->email) {
                Mail::to($client->email)->send(new RelanceMail([
                    'type'       => 'task',
                    'clientName' => $client->company_name,
                    'reference'  => $task->titre,
                    'amount'     => null,
                    'dueDate'    => \Carbon\Carbon::parse($task->date_echeance)->format('d/m/Y'),
                ]));
            }

            $message = "Rappel envoyé pour la tâche '{$task->titre}'.";
        }

        return redirect()->route('gel-secretary.relances.index')->with('success', $message);
    }
}
