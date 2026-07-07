<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
