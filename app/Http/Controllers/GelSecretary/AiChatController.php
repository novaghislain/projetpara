<?php

namespace App\Http\Controllers\GelSecretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AnthropicService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $clients = Client::all();
        $activeClientId = session('active_client_id') ?? $user->active_client_id ?? ($clients->first()?->id);
        
        $activeClient = $activeClientId ? Client::find($activeClientId) : null;
        $clientName = $activeClient ? $activeClient->company_name : 'tous les clients';

        $aiService = new AnthropicService();
        $system = "Tu es l'Assistant IA pour le portail GEL-Secrétariat. Le client actif actuel est '{$clientName}'. Le secrétaire te pose une question. Réponds de manière concise, professionnelle, et utile. Limite ta réponse à 2-3 phrases.";
        
        $response = $aiService->generate($request->message, $system, ['max_tokens' => 300]);
        
        AuditLogService::log('IA ACTION', $user, null, ['action' => 'Chat Assistant IA', 'query' => substr($request->message, 0, 100)]);
        
        return response()->json([
            'reply' => $response
        ]);
    }
}
