<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $sender;
    protected string $namespace;

    public function __construct()
    {
        $this->baseUrl = config('services.infobip.base_url', env('INFOBIP_BASE_URL', 'https://api.infobip.com'));
        $this->apiKey = config('services.infobip.api_key', env('WHATSAPP_INFOBIP_KEY', env('INFOBIP_API_KEY')));
        $this->sender = config('services.infobip.whatsapp_sender', env('WHATSAPP_SENDER', 'GEL Cabinet'));
        $this->namespace = config('services.infobip.whatsapp_namespace', env('WHATSAPP_NAMESPACE', ''));
    }

    /**
     * Envoyer un message texte simple via WhatsApp
     */
    public function sendTextMessage(string $to, string $message): bool
    {
        if (empty($this->apiKey) || empty($to)) {
            Log::warning('WhatsAppService: API Key ou destinataire manquant.');
            return false;
        }

        // Formater le numéro au format international (sans le +)
        $to = ltrim($to, '+');

        $payload = [
            'from' => $this->sender,
            'to' => $to,
            'messageId' => uniqid('WA_'),
            'content' => [
                'text' => $message
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'App ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/whatsapp/1/message/text", $payload);

        if ($response->successful()) {
            Log::info("WhatsApp envoyé à {$to}");
            return true;
        }

        Log::error('WhatsApp Text Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return false;
    }

    /**
     * Envoyer un document (PDF) via WhatsApp
     */
    public function sendDocument(string $to, string $documentUrl, string $caption = ''): bool
    {
        if (empty($this->apiKey) || empty($to)) {
            return false;
        }

        $to = ltrim($to, '+');

        $payload = [
            'from' => $this->sender,
            'to' => $to,
            'messageId' => uniqid('WA_DOC_'),
            'content' => [
                'mediaUrl' => $documentUrl,
                'caption' => $caption
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'App ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/whatsapp/1/message/document", $payload);

        if ($response->successful()) {
            return true;
        }

        Log::error('WhatsApp Document Error', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return false;
    }
}
