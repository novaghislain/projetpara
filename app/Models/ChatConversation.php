<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle représentant une conversation de chat avec l'IA.
 *
 * Stocke les échanges entre un utilisateur et l'assistant IA
 * dans le cadre d'une session de questions-réponses. Chaque
 * conversation est liée à un utilisateur et à un cabinet,
 * avec un historique des messages stocké en JSON.
 *
 * @property int $id
 * @property int $user_id Identifiant de l'utilisateur
 * @property int|null $cabinet_id Identifiant du cabinet
 * @property string $title Titre de la conversation
 * @property array $messages Messages échangés (JSON)
 * @property string $statut Statut (active, archivee)
 * @property string|null $contexte Contexte de la conversation
 * @property string|null $source Source (chat, api, etc.)
 * @property array|null $metadata Métadonnées
 *
 * @property-read User $user Utilisateur associé
 * @property-read Cabinet|null $cabinet Cabinet associé
 * @property-read \Illuminate\Database\Eloquent\Collection|IaMessage[] $iaMessages Messages IA
 *
 * @table chat_conversations
 */
class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'cabinet_id', 'title', 'messages',
        'statut', 'contexte', 'source', 'metadata',
    ];

    protected $casts = [
        'messages' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

    public function iaMessages(): HasMany
    {
        return $this->hasMany(IaMessage::class, 'conversation_id');
    }
}
