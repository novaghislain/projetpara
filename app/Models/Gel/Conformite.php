<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conformite extends Model
{
    use SoftDeletes;

    protected $table = 'gel_conformite';

    protected $fillable = [
        'client_id', 'code', 'categorie', 'titre', 'description',
        'statut', 'date_expiration', 'date_validation', 'document_path',
        'notes', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'date_expiration' => 'date',
        'date_validation' => 'date',
        'verified_at'     => 'datetime',
    ];

    // Libellés des codes d'obligations
    public static array $CODES = [
        'RCCM'        => ['titre' => 'Registre du Commerce (RCCM)',     'categorie' => 'administratif'],
        'IFU'         => ['titre' => 'Identifiant Fiscal Unique (IFU)', 'categorie' => 'fiscal'],
        'CNSS'        => ['titre' => 'Immatriculation CNSS',            'categorie' => 'social'],
        'ASSURANCE'   => ['titre' => 'Assurance professionnelle',       'categorie' => 'administratif'],
        'PATENTE'     => ['titre' => 'Patente / Licence',               'categorie' => 'fiscal'],
        'AUTORISATION'=> ['titre' => 'Autorisation sectorielle',        'categorie' => 'sectoriel'],
        'TVA'         => ['titre' => 'Déclaration TVA à jour',          'categorie' => 'fiscal'],
        'IS'          => ['titre' => 'Déclaration IS à jour',           'categorie' => 'fiscal'],
        'BILAN'       => ['titre' => 'Bilan annuel déposé',             'categorie' => 'fiscal'],
        'SALARIE_DOC' => ['titre' => 'Documents salariés à jour',       'categorie' => 'social'],
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function actions()
    {
        return $this->hasMany(ConformiteAction::class, 'conformite_id');
    }

    // Couleur selon statut
    public function getColorAttribute(): string
    {
        return match($this->statut) {
            'ok'        => '#10b981',
            'attention' => '#f59e0b',
            'ko'        => '#ef4444',
            'expire'    => '#dc2626',
            default     => '#94a3b8',
        };
    }

    // Icône selon statut
    public function getIconAttribute(): string
    {
        return match($this->statut) {
            'ok'        => 'fa-circle-check',
            'attention' => 'fa-triangle-exclamation',
            'ko'        => 'fa-circle-xmark',
            'expire'    => 'fa-clock',
            default     => 'fa-circle-question',
        };
    }

    // Valeur numérique pour le score (ok=10, attention=5, ko=0, expire=0)
    public function getScoreValueAttribute(): int
    {
        return match($this->statut) {
            'ok'        => 10,
            'attention' => 5,
            default     => 0,
        };
    }
}
