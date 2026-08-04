<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle SavedReport (Rapport sauvegardé).
 *
 * Stocke les rapports personnalisés sauvegardés par les utilisateurs.
 * Chaque rapport conserve sa configuration (filtres, colonnes, options)
 * et peut être partagé entre utilisateurs du cabinet/client.
 * Table associée : `gel_saved_reports`.
 * Supporte la suppression douce (SoftDeletes).
 *
 * @property int $id
 * @property int $cabinet_id ID du cabinet
 * @property int $client_id ID du client
 * @property string $nom Nom du rapport
 * @property string $type Type de rapport (balance, grand-livre, journal, etc.)
 * @property array $filtres Filtres appliqués (JSON)
 * @property array $colonnes Colonnes affichées (JSON)
 * @property array $configuration Configuration complète (JSON)
 * @property bool $partage Si le rapport est partagé
 * @property string|null $couleur Couleur d'identification
 *
 * @property-read \App\Models\Gel\Cabinet $cabinet Cabinet associé
 * @property-read \App\Models\Gel\Client $client Client associé
 */
class SavedReport extends Model
{
    use SoftDeletes;

    protected $table = 'gel_saved_reports';

    protected $fillable = [
        'cabinet_id', 'client_id', 'nom', 'type',
        'filtres', 'colonnes', 'configuration', 'partage', 'couleur',
    ];

    protected $casts = [
        'filtres' => 'array',
        'colonnes' => 'array',
        'configuration' => 'array',
        'partage' => 'boolean',
    ];

    // ─── Relations ───
    public function cabinet() { return $this->belongsTo(Cabinet::class); }
    public function client() { return $this->belongsTo(Client::class); }

    // ─── Scopes ───
    public function scopePartage($q) { return $q->where('partage', true); }
    public function scopeByType($q, $t) { return $q->where('type', $t); }
}
