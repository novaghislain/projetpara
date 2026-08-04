<?php

namespace App\Models\GelAdmin;

use Illuminate\Database\Eloquent\Model;
use App\Models\Gel\Cabinet;

class CabinetDocument extends Model
{
    protected $table = 'cabinet_documents';

    protected $fillable = [
        'cabinet_id',
        'type',
        'titre',
        'chemin',
    ];

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class, 'cabinet_id');
    }
}
