<?php

namespace App\Http\Controllers\Gel;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\ChatConversation;
use App\Models\IaMessage;
use App\Services\IA\ChatAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatIAController extends Controller
{
    protected ChatAiService $chatAi;

    public function __construct(ChatAiService $chatAi)
    {
        $this->chatAi = $chatAi;
    }

    /**
     * Affiche la page de chat IA.
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
     * Crée une nouvelle conversation.
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
     * Envoie un message dans une conversation.
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

        // Récupérer historique
        $historique = IaMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'role' => $m->role,
                'content' => $m->content,
            ])
            ->toArray();

        // Appel au service IA
        $response = $this->chatAi->generate(
            $validated['message'],
            $historique,
            $conversation->contexte ?? 'general',
            $conversation->cabinet_id
        );

        // Sauvegarder la réponse
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

        // Mettre à jour le message JSON dans la conversation
        $messages = $conversation->messages ?? [];
        $messages[] = ['role' => 'user', 'content' => $validated['message']];
        $messages[] = ['role' => 'assistant', 'content' => $response['message']];
        $conversation->update(['messages' => $messages]);

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
     * Récupère l'historique d'une conversation.
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
     * Supprime une conversation.
     */
    public function deleteConversation($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        // Supprimer les messages liés
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
     * Récupère les suggestions rapides.
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
     * Exporte une conversation en PDF.
     */
    public function exportConversation($conversationId, $format = 'pdf')
    {
        $conversation = ChatConversation::with('user')->findOrFail($conversationId);
        $messages = IaMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($format === 'txt') {
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

        // Fallback JSON
        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }
}
