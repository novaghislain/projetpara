<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfilProfessionnel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'gel_profil_professionnels';

    protected $fillable = [
        'user_id',
        'type',
        'domaine_expertise',
        'disponibilites',
        'tarif_horaire',
        'statut_validation',
        'bio',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
