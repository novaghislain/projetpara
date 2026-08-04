<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modèle représentant une conversation IA d'une entreprise.
 *
 * Stocke les échanges entre un utilisateur d'une entreprise cliente
 * et l'assistant IA, spécifiquement dans le contexte de l'entreprise.
 * Les messages sont stockés au format JSON.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $user_id Identifiant de l'utilisateur
 * @property string $title Titre de la conversation
 * @property array $messages Messages échangés (JSON)
 * @property string|null $context Contexte métier
 *
 * @property-read Client|null $client Client (entreprise) associé
 * @property-read User|null $user Utilisateur associé
 *
 * @table company_ai_chats
 */
class CompanyAiChat extends Model
{
    protected $fillable = [
        'client_id',
        'user_id',
        'title',
        'messages',
        'context',
    ];

    protected function casts(): array
    {
        return [
            'messages' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
