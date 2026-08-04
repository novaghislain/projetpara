<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyAiChat;
use App\Models\Document;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de l'assistant IA (Company).
 *
 * Gère le chat avec l'intelligence artificielle, l'analyse de documents,
 * la classification de contenus et la suggestion de réponses.
 *
 * Utilise le service AiService pour déléguer les appels au modèle de langage.
 */
class AiController extends BaseCompanyController
{
    protected AiService $aiService;

    /**
     * Constructeur : injecte le service IA.
     *
     * @param AiService $aiService Service de communication avec le modèle de langage
     */
    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Affiche la page de l'assistant IA (vue SPA).
     *
     * Vérifie que l'utilisateur a une entreprise associée avant d'afficher la vue.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
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
     * Envoie un message au chat IA et retourne la réponse générée.
     *
     * Construit le contexte, appelle le service AI, sauvegarde la conversation
     * dans CompanyAiChat et retourne la réponse au format JSON.
     *
     * @param Request $request Requête HTTP (message, contexte optionnel)
     * @return \Illuminate\Http\JsonResponse
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
     *
     * Récupère le document par son ID, vérifie l'appartenance au client,
     * puis délègue l'analyse et la classification au service AI.
     *
     * @param Request $request Requête HTTP (document_id)
     * @return \Illuminate\Http\JsonResponse
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
     * Classifie un document ou un texte (titre/contenu).
     *
     * Utilise le service IA pour déterminer la catégorie et le type
     * du document à partir de son titre et éventuellement de son contenu.
     *
     * @param Request $request Requête HTTP (title, content optionnel)
     * @return \Illuminate\Http\JsonResponse
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
     * Suggère une réponse IA selon le type de requête et le contexte.
     *
     * Utile pour générer des réponses types automatiques (email, accusé de
     * réception, etc.) basées sur un type de requête et un contexte donnés.
     *
     * @param Request $request Requête HTTP (query_type, context)
     * @return \Illuminate\Http\JsonResponse
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
