<?php

namespace App\Models\Gel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcritureComptable extends Model
{
    use SoftDeletes;

    protected $table = 'gel_ecritures';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'journal_id',
        'exercice_id',
        'numero',
        'date_ecriture',
        'date_piece',
        'ref_piece',
        'libelle',
        'total_debit',
        'total_credit',
        'valide',
        'valide_at',
        'valide_par',
        'createur_id',
        'notes',
    ];

    protected $casts = [
        'date_ecriture' => 'date',
        'date_piece' => 'date',
        'valide' => 'boolean',
        'valide_at' => 'datetime',
        'total_debit' => 'decimal:0',
        'total_credit' => 'decimal:0',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function lignes()
    {
        return $this->hasMany(LigneEcriture::class, 'ecriture_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function scopeValide($query)
    {
        return $query->where('valide', true);
    }

    public function scopeNonValide($query)
    {
        return $query->where('valide', false);
    }

    public function estEquilibree(): bool
    {
        return $this->total_debit === $this->total_credit;
    }
}
