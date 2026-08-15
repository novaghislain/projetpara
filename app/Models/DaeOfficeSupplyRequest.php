<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeOfficeSupplyRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'dae_office_supply_requests';

    protected $fillable = [
        'user_id',
        'client_id',
        'statut',
        'items',
        'notes'
    ];

}
