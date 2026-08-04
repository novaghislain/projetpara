<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant une suggestion générée par l'IA.
 *
 * Stocke les suggestions faites par les agents IA aux utilisateurs.
 * Chaque suggestion a un titre, une description et des données associées.
 * Elle peut être approuvée, rejetée ou en attente, avec suivi
 * de la lecture et de la décision de l'utilisateur.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property int|null $user_id Identifiant de l'utilisateur destinataire
 * @property string $agent Nom de l'agent IA émetteur
 * @property string $type Type de suggestion
 * @property string $title Titre de la suggestion
 * @property string|null $description Description détaillée
 * @property array|null $data Données de la suggestion (JSON)
 * @property array|null $metadata Métadonnées additionnelles
 * @property string $status Statut (pending, approved, rejected)
 * @property int|null $approved_by Identifiant de l'approbateur
 * @property string|null $approved_at Date d'approbation
 * @property string|null $rejection_reason Motif du rejet
 * @property string|null $read_at Date de lecture
 *
 * @property-read Client|null $client Client associé
 * @property-read User|null $user Utilisateur destinataire
 * @property-read User|null $approver Utilisateur approbateur
 *
 * @table ai_suggestions
 */
class AiSuggestion extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'agent',
        'type',
        'title',
        'description',
        'data',
        'metadata',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'metadata' => 'array',
            'approved_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    // ── Scopes ──

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // ── Relations ──

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
