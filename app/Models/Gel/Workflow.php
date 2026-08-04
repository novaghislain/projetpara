<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Workflow (Workflow automatisé).
 *
 * Définit des règles d'automatisation pour les processus métier.
 * Un workflow contient des conditions (déclencheurs) et des actions
 * à exécuter automatiquement selon une fréquence définie.
 * Table associée : `gel_workflows`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int $client_id ID du client
 * @property string $nom Nom du workflow
 * @property string $type Type de workflow
 * @property array $conditions Conditions de déclenchement (JSON)
 * @property array $actions Actions à exécuter (JSON)
 * @property bool $actif Si le workflow est actif
 * @property string|null $frequence Fréquence d'exécution
 * @property \Carbon\Carbon|null $dernier_execution Date de dernière exécution
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client $client Client associé
 */
class Workflow extends Model
{
    use SoftDeletes;

    protected $table = 'gel_workflows';

    protected $fillable = [
        'cabinet_id', 'client_id', 'nom', 'type', 'conditions',
        'actions', 'actif', 'frequence', 'dernier_execution',
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'actif' => 'boolean',
        'dernier_execution' => 'datetime',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }
    public function client() { return $this->belongsTo(Client::class); }

    // ─── Scopes ───
    public function scopeActif($q) { return $q->where('actif', true); }
    public function scopeByType($q, $t) { return $q->where('type', $t); }
}
