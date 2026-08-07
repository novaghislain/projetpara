<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class MarketingStat extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'campaign_id',
        'platform',
        'followers_gained',
        'reach',
        'engagement',
        'spend',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'date',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }
}
