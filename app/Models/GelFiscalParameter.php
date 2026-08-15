<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelFiscalParameter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_fiscal_parameters';

    protected $fillable = [
        'entreprise_id',
        'pays_code',
        'regime_fiscal',
        'periodicite_tva',
        'parametres'
    ];

}
