<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CabinetSubscriptionInvoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cabinet_subscription_invoices';

    protected $fillable = [
        'cabinet_id',
        'numero',
        'montant',
        'statut',
        'date_facture',
        'date_echeance'
    ];

}
