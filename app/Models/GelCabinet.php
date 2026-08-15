<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelCabinet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_cabinets';

    protected $fillable = [
        'nom',
        'slug',
        'email',
        'telephone',
        'adresse',
        'ville',
        'ifu',
        'rc',
        'logo',
        'actif',
        'statut',
        'config',
        'limits',
        'plan',
        'trial_ends_at',
        'subscribed_at'
    ];

    public function gelWorkflows()
    {
        return $this->hasMany(GelWorkflow::class, 'cabinet_id', 'id');
    }

}
