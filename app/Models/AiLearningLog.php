<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle représentant un journal d'apprentissage de l'IA.
 *
 * Enregistre les entrées et sorties des agents IA pour l'apprentissage
 * automatique. Permet de stocker les corrections manuelles apportées
 * aux suggestions de l'IA, ainsi que les métadonnées associées,
 * afin d'améliorer les futures prédictions.
 *
 * @property int $id
 * @property int|null $client_id Identifiant du client (entreprise)
 * @property string $agent Nom de l'agent IA
 * @property string $type Type d'interaction
 * @property mixed $input_data Données d'entrée fournies à l'IA
 * @property mixed $output_data Données de sortie générées par l'IA
 * @property mixed|null $correction Correction apportée par l'utilisateur
 * @property array|null $metadata Métadonnées additionnelles
 * @property int|null $user_id Identifiant de l'utilisateur
 *
 * @property-read Client|null $client Client associé
 * @property-read User|null $user Utilisateur associé
 *
 * @table ai_learning_log
 */
class AiLearningLog extends Model
{
    protected $table = 'ai_learning_log';

    protected $fillable = [
        'client_id',
        'agent',
        'type',
        'input_data',
        'output_data',
        'correction',
        'metadata',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    // ── Scopes ──

    public function scopeByClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByAgent($query, string $agent)
    {
        return $query->where('agent', $agent);
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
}
