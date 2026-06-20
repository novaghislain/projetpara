<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeEmail extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_emails';

    protected $fillable = [
        'client_id', 'reference_message', 'from_address', 'to_addresses',
        'cc_addresses', 'objet', 'corps_html', 'corps_texte', 'pieces_jointes',
        'statut', 'dossier', 'date_reception', 'date_envoi', 'lu_at',
        'reponse_a_id', 'tags', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'to_addresses' => 'array',
            'cc_addresses' => 'array',
            'pieces_jointes' => 'array',
            'tags' => 'array',
            'date_reception' => 'datetime',
            'date_envoi' => 'datetime',
            'lu_at' => 'datetime',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'emails';
    }

    public function reponseA()
    {
        return $this->belongsTo(DaeEmail::class, 'reponse_a_id');
    }

    public function reponses()
    {
        return $this->hasMany(DaeEmail::class, 'reponse_a_id');
    }

    public function scopeNonLu($query)
    {
        return $query->where('statut', 'recu');
    }

    public function scopeDansDossier($query, string $dossier)
    {
        return $query->where('dossier', $dossier);
    }
}
