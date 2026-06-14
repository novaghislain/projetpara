<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPole extends Model
{
    protected $table = 'client_pole';

    protected $fillable = [
        'client_id',
        'pole_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pole()
    {
        return $this->belongsTo(Pole::class);
    }
}
