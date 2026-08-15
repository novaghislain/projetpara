<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DunningLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'action_type',
        'content',
    ];

    public function campaign()
    {
        return $this->belongsTo(DunningCampaign::class, 'campaign_id');
    }
}
