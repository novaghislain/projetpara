<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyAiChat;
use App\Models\Document;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiController extends BaseCompanyController
{
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Affiche la page de l'assistant IA.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->client_id) {
            return redirect()->route('home');
        }

        return view('company', [
            'page' => 'company-ai-assistant',
            'clientId' => $user->client_id,
        ]);
    }

    /**
     * Envoie un message au chat IA et retourne la réponse.
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:4000',
            'context' => 'nullable|string|max:2000',
        ]);

        $clientId = $this->getClientId();
        $userId = Auth::id();
        $message = $request->input('message');
        $context = $request->input('context', '');

        // Construire l'historique des messages
        $messages = [
            ['role' => 'user', 'content' => $message],
        ];

        // Si un contexte est fourni, le joindre
        $fullContext = $context;
        if ($context) {
            $fullContext = "Contexte: {$context}\n\nQuestion: {$message}";
        }

        // Obtenir la réponse de l'IA
        $response = $this->aiService->chat($messages, $fullContext);

        // Sauvegarder la conversation
        $chatData = [
            ['role' => 'user', 'content' => $message],
            ['role' => 'assistant', 'content' => $response],
        ];

        $title = mb_substr($message, 0, 100) . (mb_strlen($message) > 100 ? '...' : '');

        CompanyAiChat::create([
            'client_id' => $clientId,
            'user_id' => $userId,
            'title' => $title,
            'messages' => $chatData,
            'context' => $context ?: null,
        ]);

        return response()->json([
            'response' => $response,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Analyse un document via IA (OCR et classification).
     */
    public function analyzeDocument(Request $request)
    {
        $request->validate([
            'document_id' => 'required|integer|exists:documents,id',
        ]);

        $clientId = $this->getClientId();
        $documentId = $request->input('document_id');

        $document = Document::where('client_id', $clientId)->findOrFail($documentId);

        $analysis = $this->aiService->analyzeDocument($document->file_path, $document->mime_type);
        $classification = $this->aiService->classifyDocument($document->original_name);

        return response()->json([
            'success' => true,
            'analysis' => $analysis,
            'classification' => $classification,
        ]);
    }

    /**
     * Classifie un document ou un texte.
     */
    public function classify(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:500',
            'content' => 'nullable|string|max:10000',
        ]);

        $classification = $this->aiService->classifyDocument(
            $request->input('title'),
            $request->input('content')
        );

        return response()->json($classification);
    }

    /**
     * Suggère une réponse IA selon le type de requête.
     */
    public function suggestResponse(Request $request)
    {
        $request->validate([
            'query_type' => 'required|string|max:100',
            'context' => 'nullable|string|max:2000',
        ]);

        $queryType = $request->input('query_type');
        $context = $request->input('context', '');

        $messages = [
            ['role' => 'user', 'content' => "Type: {$queryType}\nContexte: {$context}"],
        ];

        $response = $this->aiService->chat($messages, "Suggestion pour {$queryType}: {$context}");

        return response()->json([
            'suggestion' => $response,
            'query_type' => $queryType,
        ]);
    }
}
