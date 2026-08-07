<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class MarketingContent extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'campaign_id',
        'title',
        'description',
        'file_path',
        'status',
        'client_feedback',
        'publish_date',
        'platform',
        'theme',
        'content_format',
        'pillar',
        'keywords',
        'rsd',
        'published_link',
    ];

    protected $casts = [
        'publish_date' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }
}
