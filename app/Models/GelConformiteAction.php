<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelConformiteAction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_conformite_actions';

    protected $fillable = [
        'conformite_id',
        'user_id',
        'action',
        'statut'
    ];

    public function conformite()
    {
        return $this->belongsTo(GelConformite::class, 'conformite_id', 'id');
    }

}
