<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCompanyInfo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'legal_company_infos';

    protected $fillable = [
        'client_id',
        'forme_juridique',
        'capital',
        'rccm',
        'ifu',
        'date_creation',
        'dirigeants',
        'associes'
    ];

}
