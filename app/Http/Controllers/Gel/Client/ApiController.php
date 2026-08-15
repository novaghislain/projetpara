<?php

namespace App\Http\Controllers\Gel\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Api\ApiKey;
use App\Models\Api\WebhookEndpoint;

class ApiController extends Controller
{
    public function generateApiKey(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'name' => 'required|string'
        ]);

        $key = 'gel_' . Str::random(40);

        $apiKey = ApiKey::create([
            'client_id' => $request->client_id,
            'name' => $request->name,
            'key' => hash('sha256', $key) // on stocke le hash pour la sécurité
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $apiKey->name,
                'key' => $key // On ne la retourne qu'une seule fois
            ],
            'message' => 'Clé API générée. Conservez-la en lieu sûr.'
        ]);
    }

    public function registerWebhook(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'url' => 'required|url',
            'events' => 'required|array'
        ]);

        $secret = Str::random(32);

        $webhook = WebhookEndpoint::create([
            'client_id' => $request->client_id,
            'url' => $request->url,
            'events' => $request->events,
            'secret' => $secret
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $webhook,
            'message' => 'Webhook enregistré.'
        ]);
    }
}
