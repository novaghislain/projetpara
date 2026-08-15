<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleEntreprise extends Model
{
    protected $table = 'module_entreprise';
    public $timestamps = false;

    protected $fillable = [
        'entreprise_id',
        'module_code',
        'actif',
    ];

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id');
    }
}
