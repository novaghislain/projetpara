<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'envoi de messages WhatsApp Business.
 */
class WhatsAppService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'infobip');
    }

    /**
     * Envoyer un message texte WhatsApp.
     */
    public function sendText(string $to, string $message): array
    {
        return match ($this->provider) {
            'infobip' => $this->sendViaInfobip($to, $message),
            'twilio'  => $this->sendViaTwilio($to, $message),
            default   => ['success' => false, 'error' => "Provider {$this->provider} non supporté."],
        };
    }

    /**
     * Envoyer un message template WhatsApp.
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = []): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . config('services.whatsapp.infobip.api_key'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.whatsapp.infobip.base_url') . '/whatsapp/1/message/template', [
                'from' => config('services.whatsapp.infobip.sender'),
                'to'   => $to,
                'content' => [
                    'templateName' => $templateName,
                    'templateData' => ['body' => ['placeholders' => $parameters]],
                ],
            ]);

            return $response->successful()
                ? ['success' => true, 'message_id' => $response->json('messageId')]
                : ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('WhatsApp template error', ['to' => $to, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoyer un document PDF via WhatsApp.
     */
    public function sendDocument(string $to, string $mediaUrl, string $filename, ?string $caption = null): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . config('services.whatsapp.infobip.api_key'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.whatsapp.infobip.base_url') . '/whatsapp/1/message/document', [
                'from' => config('services.whatsapp.infobip.sender'),
                'to'   => $to,
                'content' => [
                    'mediaUrl' => $mediaUrl,
                    'filename' => $filename,
                    'caption'  => $caption,
                ],
            ]);

            return $response->successful()
                ? ['success' => true, 'message_id' => $response->json('messageId')]
                : ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('WhatsApp document error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaInfobip(string $to, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . config('services.whatsapp.infobip.api_key'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.whatsapp.infobip.base_url') . '/whatsapp/1/message/text', [
                'from' => config('services.whatsapp.infobip.sender'),
                'to'   => $to,
                'content' => ['text' => $message],
            ]);

            return $response->successful()
                ? ['success' => true, 'message_id' => $response->json('messageId')]
                : ['success' => false, 'error' => $response->body()];
        } catch (\Exception $e) {
            Log::error('WhatsApp Infobip error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaTwilio(string $to, string $message): array
    {
        try {
            $response = Http::withBasicAuth(
                config('services.whatsapp.twilio.account_sid'),
                config('services.whatsapp.twilio.auth_token')
            )->asForm()->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.whatsapp.twilio.account_sid') . "/Messages.json", [
                'From' => config('services.whatsapp.twilio.from'),
                'To'   => "whatsapp:$to",
                'Body' => $message,
            ]);

            return $response->successful()
                ? ['success' => true, 'message_id' => $response->json('sid')]
                : ['success' => false, 'error' => $response->json('message')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
