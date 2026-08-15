<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingBudgetLine extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'accounting_budget_lines';

    protected $fillable = [
        'budget_id',
        'compte',
        'montant_prevu',
        'montant_reel'
    ];

}
