<?php

namespace App\Models\Dae;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class DaeModeleCourrier extends DaeBaseModel
{
    use SoftDeletes;

    protected $table = 'dae_modeles_courriers';

    protected $fillable = [
        'client_id', 'nom', 'type', 'objet_defaut', 'corps',
        'variables', 'categorie', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
        ];
    }

    protected function getDaeModuleName(): string
    {
        return 'modeles';
    }
}
