<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\ChatConversation;
use App\Models\IaMessage;
use App\Services\IA\ChatAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de chat avec l'intelligence artificielle.
 * Gère les conversations, l'envoi et la réception de messages,
 * l'historique, les suggestions rapides et l'export des conversations.
 * Utilise le service ChatAiService pour la génération des réponses.
 */
class ChatIAController extends Controller
{
    /** @var ChatAiService Service d'IA conversationnelle */
    protected ChatAiService $chatAi;

    /**
     * Initialise le contrôleur avec le service de chat IA.
     *
     * @param ChatAiService $chatAi Service de chat IA
     */
    public function __construct(ChatAiService $chatAi)
    {
        $this->chatAi = $chatAi;
    }

    /**
     * Affiche la page de chat IA avec les 20 dernières conversations.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $conversations = ChatConversation::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('gel.ia.chat', compact('conversations'));
    }

    /**
     * Crée une nouvelle conversation avec un titre et un contexte optionnel.
     * Enregistre une trace d'audit de la création.
     *
     * @param Request $request La requête HTTP contenant title et contexte
     * @return \Illuminate\Http\JsonResponse
     */
    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contexte' => 'nullable|string|max:100',
        ]);

        $conversation = ChatConversation::create([
            'user_id' => Auth::id(),
            'cabinet_id' => Auth::user()->cabinet_id,
            'title' => $validated['title'],
            'contexte' => $validated['contexte'] ?? 'general',
            'statut' => 'active',
            'source' => 'gel',
            'messages' => [],
            'metadata' => null,
        ]);

        // Enregistrer la création dans les traces d'audit
        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_conversation_create',
            'auditable_type' => ChatConversation::class,
            'auditable_id' => $conversation->id,
            'description' => 'Nouvelle conversation IA : ' . $validated['title'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Envoie un message dans une conversation et reçoit une réponse IA.
     * Sauvegarde le message utilisateur, récupère l'historique, appelle le service IA,
     * sauvegarde la réponse et met à jour la conversation.
     *
     * @param Request $request La requête HTTP contenant le message
     * @param int $conversationId L'identifiant de la conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $conversation = ChatConversation::findOrFail($conversationId);

        // Sauvegarder le message utilisateur
        $userMessage = IaMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // Récupérer tout l'historique des messages pour le contexte
        $historique = IaMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->toArray();

        // Appel au service IA pour générer une réponse
        $response = $this->chatAi->generate(
            $validated['message'],
            $historique,
            $conversation->contexte ?? 'general',
            $conversation->cabinet_id
        );

        // Sauvegarder la réponse de l'assistant
        $assistantMessage = IaMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'role' => 'assistant',
            'content' => $response['message'],
            'metadata' => [
                'intent' => $response['intent'] ?? null,
                'model' => $response['model'] ?? null,
            ],
        ]);

        // Mettre à jour le champ JSON contenant les messages dans la conversation
        $messages = $conversation->messages ?? [];
        $messages[] = ['role' => 'user', 'content' => $validated['message']];
        $messages[] = ['role' => 'assistant', 'content' => $response['message']];
        $conversation->update(['messages' => $messages]);

        // Enregistrer l'envoi du message dans les traces d'audit
        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_message_sent',
            'auditable_type' => ChatConversation::class,
            'auditable_id' => $conversation->id,
            'description' => 'Message IA : ' . substr($validated['message'], 0, 100),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $response['message'],
            'intent' => $response['intent'] ?? null,
            'actions' => $response['actions'] ?? [],
            'suggestions' => $response['suggestions'] ?? [],
            'model' => $response['model'] ?? null,
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
        ]);
    }

    /**
     * Récupère l'historique complet des messages d'une conversation.
     *
     * @param int $conversationId L'identifiant de la conversation
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        $messages = IaMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'metadata' => $m->metadata,
                'created_at' => $m->created_at,
            ]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    /**
     * Supprime une conversation et tous ses messages associés.
     *
     * @param int $conversationId L'identifiant de la conversation à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteConversation($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        // Supprimer les messages liés avant la conversation elle-même
        IaMessage::where('conversation_id', $conversation->id)->delete();
        $conversation->delete();

        AuditTrail::create([
            'user_id' => Auth::id(),
            'event' => 'ia_conversation_delete',
            'auditable_type' => ChatConversation::class,
            'auditable_id' => $conversationId,
            'description' => 'Conversation IA supprimée',
        ]);

        return response()->json(['success' => true, 'message' => 'Conversation supprimée.']);
    }

    /**
     * Récupère les suggestions rapides pour le chat IA.
     *
     * @param Request $request La requête HTTP contenant contexte et query optionnels
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSuggestions(Request $request)
    {
        $contexte = $request->get('contexte', 'general');
        $query = $request->get('query', '');

        $suggestions = $this->chatAi->suggestions($contexte, $query);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Exporte une conversation aux formats texte ou JSON.
     * Génère un fichier texte brut téléchargeable pour le format TXT,
     * ou retourne la conversation en JSON pour les autres formats.
     *
     * @param int $conversationId L'identifiant de la conversation à exporter
     * @param string $format Le format d'export (txt par défaut)
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function exportConversation($conversationId, $format = 'pdf')
    {
        $conversation = ChatConversation::with('user')->findOrFail($conversationId);
        $messages = IaMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($format === 'txt') {
            // Générer un fichier texte avec l'en-tête et les messages formatés
            $content = "Conversation : {$conversation->title}\n";
            $content .= "Date : {$conversation->created_at->format('d/m/Y H:i')}\n";
            $content .= str_repeat('=', 50) . "\n\n";

            foreach ($messages as $msg) {
                $role = $msg->role === 'user' ? 'Moi' : 'Assistant';
                $content .= "[{$role}] {$msg->created_at->format('H:i')} :\n{$msg->content}\n\n";
            }

            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="conversation-' . $conversation->id . '.txt"',
            ]);
        }

        // Fallback : retourner les données en JSON
        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }
}
