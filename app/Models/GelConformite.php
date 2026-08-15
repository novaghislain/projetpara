<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelConformite extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_conformite';

    protected $fillable = [
        'client_id',
        'domaine',
        'obligation',
        'statut',
        'date_expiration',
        'notes',
        'code',
        'categorie',
        'titre'
    ];

    public function gelConformiteActions()
    {
        return $this->hasMany(GelConformiteAction::class, 'conformite_id', 'id');
    }

}
