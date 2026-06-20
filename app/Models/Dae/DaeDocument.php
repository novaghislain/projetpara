<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeDocument extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_documents';

    protected $fillable = [
        'client_id', 'dossier_id', 'reference', 'titre', 'type_document', 'categorie',
        'description', 'fichier', 'taille_fichier', 'mime_type', 'version',
        'statut', 'date_expiration', 'alerte_expiration', 'valide', 'signe',
        'mots_cles', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'mots_cles' => 'array',
            'date_expiration' => 'date',
            'alerte_expiration' => 'boolean',
            'valide' => 'boolean',
            'signe' => 'boolean',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'documents';
    }

    public function scopeFinalises($query)
    {
        return $query->where('statut', 'final');
    }

    public function scopeExpirationProche($query, int $jours = 30)
    {
        return $query->where('alerte_expiration', true)
            ->whereNotNull('date_expiration')
            ->whereBetween('date_expiration', [today(), today()->addDays($jours)]);
    }

    // ── Relations ──

    public function dossier()
    {
        return $this->belongsTo(DaeDocumentDossier::class, 'dossier_id');
    }
}
