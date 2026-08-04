<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle IaMessage (Message IA).
 *
 * Représente un message échangé au sein d'une conversation avec l'assistant IA.
 * Chaque message a un rôle (user/assistant/system), un contenu textuel,
 * et des métadonnées optionnelles (JSON).
 * Table associée : `ia_messages`.
 *
 * @property int $id
 * @property int $conversation_id ID de la conversation parente
 * @property int $user_id ID de l'utilisateur
 * @property string $role Rôle du message (user/assistant/system)
 * @property string $content Contenu textuel du message
 * @property array|null $metadata Métadonnées additionnelles (JSON)
 *
 * @property-read \App\Models\ChatConversation $conversation Conversation parente
 * @property-read \App\Models\User $user Utilisateur émetteur
 */
class IaMessage extends Model
{
    protected $table = 'ia_messages';

    protected $fillable = [
        'conversation_id', 'user_id', 'role', 'content', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
