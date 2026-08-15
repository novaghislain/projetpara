<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeDossier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entreprise_id',
        'type_demarche',
        'statut',
        'reference_institution',
        'documents_joints',
        'meta_data',
        'notes_institution',
        'soumis_le',
        'cloture_le'
    ];

    protected function casts(): array
    {
        return [
            'documents_joints' => 'array',
            'meta_data' => 'array',
            'soumis_le' => 'datetime',
            'cloture_le' => 'datetime',
        ];
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
