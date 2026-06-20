<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service d'envoi de SMS — compatible Infobip, Twilio, etc.
 */
class SmsService
{
    private string $provider;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'infobip');
    }

    /**
     * Envoyer un SMS.
     *
     * @param string $to   Numéro au format international (+229XXXXXXXX)
     * @param string $message Contenu du message
     * @return array ['success' => bool, 'message_id' => ?string, 'error' => ?string]
     */
    public function send(string $to, string $message): array
    {
        return match ($this->provider) {
            'infobip' => $this->sendViaInfobip($to, $message),
            'twilio'  => $this->sendViaTwilio($to, $message),
            default   => ['success' => false, 'error' => "Provider {$this->provider} non supporté."],
        };
    }

    private function sendViaInfobip(string $to, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . config('services.sms.infobip.api_key'),
                'Content-Type'  => 'application/json',
            ])->post(config('services.sms.infobip.base_url') . '/sms/2/text/advanced', [
                'messages' => [[
                    'from' => config('services.sms.infobip.sender', 'GEL Cabinet'),
                    'destinations' => [['to' => $to]],
                    'text' => $message,
                ]],
            ]);

            if ($response->successful()) {
                $msgId = $response->json('messages.0.messageId');
                Log::info('SMS sent via Infobip', ['to' => $to, 'message_id' => $msgId]);
                return ['success' => true, 'message_id' => $msgId];
            }

            Log::error('Infobip SMS error', ['to' => $to, 'status' => $response->status(), 'body' => $response->body()]);
            return ['success' => false, 'error' => $response->json('requestError.serviceException.text')];
        } catch (\Exception $e) {
            Log::error('Infobip SMS exception', ['to' => $to, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendViaTwilio(string $to, string $message): array
    {
        try {
            $response = Http::withBasicAuth(
                config('services.sms.twilio.account_sid'),
                config('services.sms.twilio.auth_token')
            )->asForm()->post("https://api.twilio.com/2010-04-01/Accounts/" . config('services.sms.twilio.account_sid') . "/Messages.json", [
                'From' => config('services.sms.twilio.from'),
                'To'   => $to,
                'Body' => $message,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message_id' => $response->json('sid')];
            }

            return ['success' => false, 'error' => $response->json('message')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
