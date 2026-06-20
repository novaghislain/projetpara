<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeDocumentDossier extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_document_dossiers';

    protected $fillable = [
        'client_id', 'nom', 'parent_id', 'couleur', 'description', 'ordre', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'couleur' => 'string',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'documents';
    }

    // ── Relations ──

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('ordre')->orderBy('nom');
    }

    public function documents()
    {
        return $this->hasMany(DaeDocument::class, 'dossier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ──

    public function scopeRacines($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeAvecEnfants($query)
    {
        return $query->with(['children' => fn($q) => $q->orderBy('ordre')->orderBy('nom')]);
    }
}
