<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingGrillesTarifaire extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounting_grilles_tarifaires';

    protected $fillable = [
        'client_id',
        'designation',
        'prix',
        'categorie',
        'actif'
    ];

}
