<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle LegalAssembly (Assemblée générale).
 *
 * Gère l'organisation et le suivi des assemblées générales (AGO, AGE)
 * avec la gestion des convocations, du quorum, des résolutions,
 * et des participants. Suit le statut et le procès-verbal.
 * Table associée : `legal_assemblies`.
 *
 * @property int $id
 * @property int $client_id ID du client
 * @property string $type Type d'assemblée (AGO, AGE)
 * @property int $annee Année de l'assemblée
 * @property \Carbon\Carbon|null $date_convocation Date de convocation
 * @property \Carbon\Carbon|null $date_tenue Date de tenue
 * @property string|null $lieu Lieu de l'assemblée
 * @property float|null $quorum_requis Quorum requis (%)
 * @property float|null $quorum_atteint Quorum atteint (%)
 * @property array|null $ordre_du_jour Ordre du jour (JSON)
 * @property array|null $resolutions Résolutions adoptées (JSON)
 * @property array|null $participants Liste des participants (JSON)
 * @property string $statut Statut (planifiée, tenue, reportée, annulée)
 * @property string|null $pv_path Chemin du procès-verbal
 * @property bool $pv_approuve PV approuvé
 * @property bool $convocation_envoyée Convocation envoyée
 * @property int $created_by ID du créateur
 */
class LegalAssembly extends LegalBaseModel
{
    use HasFactory;

    protected $table = 'legal_assemblies';

    protected $fillable = [
        'client_id', 'type', 'annee',
        'date_convocation', 'date_tenue', 'lieu',
        'quorum_requis', 'quorum_atteint',
        'ordre_du_jour', 'resolutions', 'participants',
        'statut', 'pv_path', 'pv_approuve', 'convocation_envoyee',
        'created_by',
    ];

    protected $casts = [
        'ordre_du_jour' => 'json',
        'resolutions' => 'json',
        'participants' => 'json',
        'date_convocation' => 'date',
        'date_tenue' => 'date',
        'pv_approuve' => 'boolean',
        'convocation_envoyee' => 'boolean',
        'quorum_requis' => 'decimal:2',
        'quorum_atteint' => 'decimal:2',
    ];

    public function scopePlanifiees($query)
    {
        return $query->where('statut', 'planifiée');
    }

    public function scopeTenues($query)
    {
        return $query->where('statut', 'tenue');
    }
}
