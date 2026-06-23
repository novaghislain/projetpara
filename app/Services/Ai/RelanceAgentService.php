<?php

namespace App\Services\Ai;

use App\Models\AiSuggestion;
use App\Models\ErpInvoice;
use App\Models\Client;
use App\Models\RelanceRule;
use App\Services\RelanceService;

class RelanceAgentService
{
    public function __construct(
        private RelanceService $relanceService
    ) {}

    /**
     * Analyser les factures impayées et suggérer des relances intelligentes.
     */
    public function analyzeOverdue(int $clientId): array
    {
        $suggestions = [];

        // Factures échues
        $overdueInvoices = ErpInvoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->with('client')
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = now()->diffInDays($invoice->due_date);
            $clientName = $invoice->client_name ?? 'Client';

            // Ne suggérer que si la relance n'a pas déjà été faite
            $existing = AiSuggestion::byClient($clientId)
                ->byAgent('relance')
                ->where('type', 'relance_facture')
                ->where('data->invoice_id', $invoice->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existing) continue;

            $urgency = $daysOverdue <= 7 ? 'warning' : ($daysOverdue <= 30 ? 'urgent' : 'critical');

            $suggestions[] = [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'client_name' => $clientName,
                'amount' => $invoice->total_ttc,
                'days_overdue' => $daysOverdue,
                'urgency' => $urgency,
            ];
        }

        if (count($suggestions)) {
            $critical = count(array_filter($suggestions, fn($s) => $s['urgency'] === 'critical'));
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'relance',
                'type' => 'relance_facture',
                'title' => count($suggestions) . ' facture(s) en retard de paiement',
                'description' => "{$critical} facture(s) critique(s) de plus de 30 jours. Montant total impayé : " .
                    number_format(array_sum(array_column($suggestions, 'amount')), 0, ',', ' ') . ' FCFA',
                'data' => ['invoices' => $suggestions, 'total_overdue' => array_sum(array_column($suggestions, 'amount'))],
                'metadata' => ['agent' => 'Relance', 'version' => '1.0'],
                'status' => 'pending',
            ]);
            return ['suggestion_id' => $suggestion->id, 'count' => count($suggestions)];
        }

        return ['suggestion_id' => null, 'count' => 0];
    }

    /**
     * Suggérer des règles de relance optimisées.
     */
    public function suggestRules(int $clientId): array
    {
        $activeRules = RelanceRule::where('client_id', $clientId)->where('is_active', true)->count();

        if ($activeRules === 0) {
            $suggestion = AiSuggestion::create([
                'client_id' => $clientId,
                'agent' => 'relance',
                'type' => 'suggest_rules',
                'title' => 'Configurer des règles de relance',
                'description' => 'Aucune règle de relance active. Les factures impayées ne génèrent pas de relance automatique.',
                'data' => ['suggested_rules' => [
                    ['name' => 'Relance J+7', 'trigger_days' => 7, 'channel' => 'email', 'template' => 'Première relance'],
                    ['name' => 'Relance J+15', 'trigger_days' => 15, 'channel' => 'email', 'template' => 'Deuxième relance'],
                    ['name' => 'Relance J+30', 'trigger_days' => 30, 'channel' => 'whatsapp', 'template' => 'Dernier avis'],
                ]],
                'metadata' => ['agent' => 'Relance', 'version' => '1.0'],
                'status' => 'pending',
            ]);
            return ['suggestion_id' => $suggestion->id, 'count' => 0, 'needs_setup' => true];
        }

        return ['suggestion_id' => null, 'count' => $activeRules, 'needs_setup' => false];
    }

    public function summary(int $clientId): array
    {
        $pending = AiSuggestion::byClient($clientId)->byAgent('relance')->pending()->count();
        $overdueCount = ErpInvoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->count();

        return [
            'pending_suggestions' => $pending,
            'overdue_invoices' => $overdueCount,
            'status' => $overdueCount > 0 ? 'attention' : 'ok',
        ];
    }
}
