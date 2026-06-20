<?php

namespace App\Services;

use App\Models\RelanceRule;
use App\Models\ErpInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service de relances automatisées.
 *
 * Parcourt les règles actives, trouve les factures/échéances arrivées
 * à terme, et envoie les notifications via les canaux configurés.
 */
class RelanceService
{
    public function __construct(
        private SmsService $smsService,
        private WhatsAppService $whatsappService,
    ) {}

    /**
     * Exécuter toutes les règles de relance actives.
     * Retourne le nombre de relances envoyées.
     */
    public function executeAll(): int
    {
        $rules = RelanceRule::where('is_active', true)->get();
        $sent = 0;

        foreach ($rules as $rule) {
            try {
                $sent += $this->executeRule($rule);
            } catch (\Exception $e) {
                Log::error("Relance rule #{$rule->id} failed", ['error' => $e->getMessage()]);
            }
        }

        Log::info("RelanceService: $sent relances envoyées");
        return $sent;
    }

    /**
     * Exécuter une règle spécifique.
     */
    public function executeRule(RelanceRule $rule): int
    {
        $invoices = $this->findDueInvoices($rule);
        $sent = 0;

        foreach ($invoices as $invoice) {
            try {
                $success = $this->sendRelance($rule, $invoice);
                if ($success) $sent++;
            } catch (\Exception $e) {
                Log::error("Relance invoice #{$invoice->id} failed", ['error' => $e->getMessage()]);
            }
        }

        return $sent;
    }

    /**
     * Trouver les factures arrivées à échéance selon la règle.
     */
    private function findDueInvoices(RelanceRule $rule): array
    {
        $query = ErpInvoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<=', Carbon::now()->addDays($rule->trigger_days));

        // Filtrer par client si la règle est liée à un client spécifique
        if ($rule->client_id) {
            $query->where('client_id', $rule->client_id);
        }

        return $query->limit(50)->get()->all();
    }

    /**
     * Envoyer une relance pour une facture via le canal configuré.
     */
    private function sendRelance(RelanceRule $rule, ErpInvoice $invoice): bool
    {
        $client = $invoice->client;
        if (!$client) return false;

        $subject = $this->interpolate($rule->template_subject, $invoice);
        $body = $this->interpolate($rule->template_body, $invoice);

        $channel = $rule->channel ?? 'sms';

        return match ($channel) {
            'sms' => $this->sendSms($client, $body),
            'whatsapp' => $this->sendWhatsApp($client, $subject, $body),
            'email' => $this->sendEmail($client, $subject, $body),
            default => false,
        };
    }

    private function sendSms($client, string $body): bool
    {
        if (!$client->phone) return false;
        $result = $this->smsService->send($client->phone, $body);
        return $result['success'] ?? false;
    }

    private function sendWhatsApp($client, string $subject, string $body): bool
    {
        $number = $client->whatsapp_number ?? $client->phone;
        if (!$number) return false;
        $result = $this->whatsappService->sendText($number, "*$subject*\n\n$body");
        return $result['success'] ?? false;
    }

    private function sendEmail($client, string $subject, string $body): bool
    {
        if (!$client->email) return false;

        try {
            \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($client, $subject) {
                $message->to($client->email)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Relance email error", ['to' => $client->email, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Interpoler les variables dans les templates.
     * Supporte : {{client_name}}, {{invoice_number}}, {{due_date}}, {{amount}}, {{balance}}
     */
    private function interpolate(string $template, ErpInvoice $invoice): string
    {
        $client = $invoice->client;
        $replacements = [
            '{{client_name}}'    => $client->name ?? 'Client',
            '{{invoice_number}}' => $invoice->invoice_number ?? $invoice->id,
            '{{due_date}}'       => $invoice->due_date?->format('d/m/Y') ?? 'N/A',
            '{{amount}}'         => number_format((float) ($invoice->total_ttc ?? 0), 2),
            '{{balance}}'        => number_format((float) ($invoice->total_ttc ?? 0), 2),
            '{{reference}}'      => $invoice->reference ?? $invoice->invoice_number ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
