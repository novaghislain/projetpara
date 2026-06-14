<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'color', 'category', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $service) {
            $service->slug = $service->slug ?: Str::slug($service->name);
        });
    }

    // Relations
    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_service')
            ->withPivot(['status', 'start_date', 'end_date', 'settings'])
            ->withTimestamps();
    }

    public function clientServices()
    {
        return $this->hasMany(ClientService::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}
