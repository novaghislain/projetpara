<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
