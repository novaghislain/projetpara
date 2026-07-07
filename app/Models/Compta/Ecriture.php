<?php

namespace App\Models\Compta;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Client;
use App\Models\User;

class Ecriture extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'journal_id', 'exercice_id', 'date_ecriture', 'numero_piece', 
        'reference', 'libelle', 'total_debit', 'total_credit', 'is_validee', 
        'validated_by', 'validated_at', 'is_extourne', 'extourne_id', 'piece_jointe_path', 'is_recurrente'
    ];

    protected $casts = [
        'date_ecriture' => 'date',
        'validated_at' => 'datetime',
        'is_validee' => 'boolean',
        'is_extourne' => 'boolean',
        'is_recurrente' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function exercice()
    {
        return $this->belongsTo(Exercice::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneEcriture::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
