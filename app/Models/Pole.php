<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_pole')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
