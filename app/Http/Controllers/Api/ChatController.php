<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur API pour le chat avec l'assistant virtuel GEL.
 *
 * Gère l'envoi de messages, la récupération de l'historique,
 * l'affichage des conversations et les suggestions de questions.
 */
class ChatController extends BaseApiController
{
    /**
     * Envoie un message et retourne la réponse de l'assistant.
     * Crée une nouvelle conversation si aucun ID n'est fourni.
     *
     * @param Request $request La requête HTTP contenant le message et l'ID de conversation optionnel.
     * @return \Illuminate\Http\JsonResponse
     */
    public function message(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
            'conversation_id' => 'nullable|exists:chat_conversations,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422);
        }

        $user = Auth::user();
        $message = $request->input('message');
        $conversationId = $request->input('conversation_id');

        // Créer ou récupérer la conversation existante
        if ($conversationId) {
            $conversation = ChatConversation::findOrFail($conversationId);
            // Vérifier que l'utilisateur est bien le propriétaire de la conversation
            if ($conversation->user_id !== $user->id) {
                return $this->sendError('Non autorisé', [], 403);
            }
        } else {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
                'title' => mb_substr($message, 0, 100),
                'messages' => [],
            ]);
        }

        // Ajouter le message utilisateur
        $messages = $conversation->messages;
        $messages[] = [
            'role' => 'user',
            'content' => $message,
            'timestamp' => now()->toIso8601String(),
        ];

        // Générer une réponse simulée
        $response = $this->generateResponse($message, $conversation);

        $messages[] = [
            'role' => 'assistant',
            'content' => $response,
            'timestamp' => now()->toIso8601String(),
        ];

        $conversation->update(['messages' => $messages]);

        return $this->sendResponse([
            'conversation_id' => $conversation->id,
            'reply' => $response,
            'conversation' => $conversation,
        ], 'Message envoyé');
    }

    /**
     * Récupère l'historique paginé des conversations de l'utilisateur.
     *
     * @param Request $request La requête HTTP.
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $conversations = ChatConversation::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return $this->sendResponse($conversations, 'Historique récupéré');
    }

    /**
     * Affiche une conversation spécifique avec ses messages.
     *
     * @param int $id L'identifiant de la conversation.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        $conversation = ChatConversation::where('user_id', $user->id)->findOrFail($id);

        return $this->sendResponse($conversation, 'Conversation récupérée');
    }

    /**
     * Supprime une conversation de l'utilisateur.
     *
     * @param int $id L'identifiant de la conversation.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $conversation = ChatConversation::where('user_id', $user->id)->findOrFail($id);
        $conversation->delete();

        return $this->sendResponse([], 'Conversation supprimée');
    }

    /**
     * Retourne une liste de suggestions de questions pour l'assistant.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function suggestions()
    {
        $suggestions = [
            'Présentez-moi GEL Cabinet',
            'Quels sont vos modules disponibles ?',
            'Comment fonctionne la comptabilité SYSCOHADA ?',
            'Quels sont les tarifs ?',
            'Comment l\'IA peut-elle m\'aider ?',
            'Quels pays couvrez-vous ?',
        ];

        return $this->sendResponse($suggestions, 'Suggestions récupérées');
    }

    /**
     * Génère une réponse simulée basée sur des mots-clés.
     * À terme, cette méthode sera remplacée par un appel à un service d'IA.
     *
     * @param string $message Le message de l'utilisateur.
     * @param ChatConversation $conversation La conversation en cours (pour le contexte).
     * @return string La réponse générée.
     */
    private function generateResponse(string $message, ChatConversation $conversation): string
    {
        $m = mb_strtolower(trim($message));

        // Salutations
        if (preg_match('/\bbonjour\b|\bsalut\b|\bbonsoir\b/', $m)) {
            return 'Bonjour ! 👋 Je suis **GEL Assistant**, votre assistant intelligent. Je peux vous renseigner sur nos modules, vous guider dans la plateforme, ou répondre à vos questions. Comment puis-je vous aider aujourd\'hui ?';
        }

        if (preg_match('/\bmodule\b|\bfonctionnalit(e|é)\b/', $m)) {
            return 'GEL Cabinet propose **15+ modules interconnectés** pour gérer votre cabinet :' . "\n\n" .
                   '📋 **CRM Clients** — Gestion des contacts, relances, opportunités' . "\n" .
                   '📁 **GED** — Documents, versioning, recherche plein texte' . "\n" .
                   '📊 **Comptabilité SYSCOHADA** — Plan comptable OHADA, journaux, bilan' . "\n" .
                   '💰 **ERP Intégré** — Stocks, factures, devis, trésorerie' . "\n" .
                   '👥 **RH & Paie** — Employés, contrats, paie Bénin' . "\n" .
                   '⚖️ **Juridique** — Contrats, assemblées, contentieux' . "\n\n" .
                   'Quel module vous intéresse en particulier ?';
        }

        if (preg_match('/\btarif\b|\bprix\b|\bco[uû]t\b|\bcombien\b/', $m)) {
            return 'Nos formules sont adaptées à la taille de votre cabinet. Je vous invite à consulter notre page **[Tarifs](/tarifs)** pour voir nos offres, ou à [nous contacter](/contact) pour une démonstration personnalisée.';
        }

        if (preg_match('/\bcomptab(ilité|ilité|le)\b|\bsysco(hada|a)\b|\bohada\b|\bbilan\b/', $m)) {
            return 'Notre **module Comptabilité** couvre intégralement les normes OHADA/SYSCOHADA :' . "\n\n" .
                   '✅ Plan comptable SYSCOHADA complet (classes 1 à 9)' . "\n" .
                   '✅ Journaux : ventes, achats, banque, caisse, OD' . "\n" .
                   '✅ Balance générale et auxiliaire' . "\n" .
                   '✅ Grand livre' . "\n" .
                   '✅ Bilan et Compte de Résultat' . "\n" .
                   '✅ Déclarations fiscales intégrées (TVA, ITS, CNSS, IS)' . "\n" .
                   '✅ Conformité e-MECeF DGI' . "\n\n" .
                   'Souhaitez-vous en savoir plus sur un aspect particulier ?';
        }

        if (preg_match('/\bia\b|\bintelligence\b|\bagent\b|\brobot\b/', $m)) {
            return '🤖 **GEL Intelligence** — 6 agents IA spécialisés pour automatiser votre cabinet :' . "\n\n" .
                   '📊 **Agent OHADA** — Catégorisation automatique, détection d\'anomalies' . "\n" .
                   '💰 **Agent Relance** — Canal optimal, escalade progressive' . "\n" .
                   '🏦 **Agent Rapprochement** — Matching bancaire intelligent' . "\n" .
                   '📋 **Agent Fiscal Bénin** — Déclarations automatisées' . "\n" .
                   '📄 **Agent OCR** — Import factures fournisseurs' . "\n" .
                   '🔮 **Agent Trésorerie** — Prédiction cash flow' . "\n\n" .
                   'Chaque suggestion est soumise à votre approbation — le comptable reste décisionnaire.';
        }

        if (preg_match('/\bcontact\b|\bsupport\b|\bt(é|e)l(é|e)phone\b|\bemail\b/', $m)) {
            return 'Vous pouvez nous joindre par :' . "\n\n" .
                   '📧 **Email** : [contact@gelcabinet.com](mailto:contact@gelcabinet.com)' . "\n" .
                   '📞 **Téléphone** : +229 XX XX XX XX' . "\n" .
                   '🕐 **Horaires** : Lundi au Vendredi, 8h00 – 18h00' . "\n\n" .
                   'Ou laissez-nous un message via notre **[page contact](/contact)** et nous vous répondrons sous 24h.';
        }

        if (preg_match('/\binscription\b|\bcr(é|e)er\b|\bcompte\b|\bs\'inscrire\b/', $m)) {
            return 'Pour créer votre espace GEL Cabinet, rendez-vous sur notre **[page d\'inscription](/register)**. Le processus est simple :' . "\n\n" .
                   '1️⃣ Créez votre compte' . "\n" .
                   '2️⃣ Renseignez les informations de votre entreprise' . "\n" .
                   '3️⃣ Activez les modules souhaités' . "\n" .
                   '4️⃣ Vous êtes opérationnel !' . "\n\n" .
                   'L\'essai est gratuit pendant 30 jours, sans engagement.';
        }

        // Réponse par défaut
        return 'Merci pour votre message ! 😊 Je suis GEL Assistant, votre assistant virtuel. Je peux vous aider sur :' . "\n\n" .
               '🔹 **Présentation** de GEL Cabinet' . "\n" .
               '🔹 **Modules** disponibles (CRM, Comptabilité, RH, etc.)' . "\n" .
               '🔹 **Tarifs** et formules' . "\n" .
               '🔹 **Intelligence Artificielle** et agents IA' . "\n" .
               '🔹 **Contact** et support' . "\n\n" .
               'Que souhaitez-vous savoir ? Posez-moi votre question !';
    }
}
