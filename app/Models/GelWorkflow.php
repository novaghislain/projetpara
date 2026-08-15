<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GelWorkflow extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'gel_workflows';

    protected $fillable = [
        'cabinet_id',
        'client_id',
        'nom',
        'type',
        'conditions',
        'actions',
        'actif',
        'frequence',
        'dernier_execution'
    ];

    public function cabinet()
    {
        return $this->belongsTo(GelCabinet::class, 'cabinet_id', 'id');
    }

}
