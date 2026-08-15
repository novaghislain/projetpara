<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelSubscription extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_subscriptions';

    protected $fillable = [
        'entreprise_id',
        'plan',
        'statut',
        'debut_le',
        'fin_le',
        'montant'
    ];

}
