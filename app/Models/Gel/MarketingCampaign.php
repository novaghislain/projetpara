<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'status',
        'budget',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    public function contents()
    {
        return $this->hasMany(MarketingContent::class, 'campaign_id');
    }

    public function stats()
    {
        return $this->hasMany(MarketingStat::class, 'campaign_id');
    }
}
