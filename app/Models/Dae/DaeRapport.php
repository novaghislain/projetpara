<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeRapport extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_rapports';

    protected $fillable = [
        'client_id', 'titre', 'type_rapport', 'description',
        'periode_debut', 'periode_fin', 'contenu', 'fichier',
        'metriques', 'statut', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'contenu' => 'array',
            'metriques' => 'array',
            'periode_debut' => 'date',
            'periode_fin' => 'date',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'rapports';
    }
}
