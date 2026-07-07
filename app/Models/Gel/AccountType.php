<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    protected $table = 'gel_account_types';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'account_type', 'code');
    }
}
