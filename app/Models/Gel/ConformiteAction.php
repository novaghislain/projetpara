<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ConformiteAction extends Model
{
    protected $table = 'gel_conformite_actions';

    protected $fillable = [
        'conformite_id', 'client_id', 'titre', 'description',
        'responsable_id', 'echeance', 'document_attendu', 'statut',
    ];

    protected $casts = [
        'echeance' => 'date',
    ];

    public function conformite()
    {
        return $this->belongsTo(Conformite::class, 'conformite_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
}
