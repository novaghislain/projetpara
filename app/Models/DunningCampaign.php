<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DunningCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'invoice_number',
        'amount_due',
        'escalation_level',
        'status',
        'next_action_at',
        'ai_predicted_payment_date',
    ];

    protected $casts = [
        'next_action_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function logs()
    {
        return $this->hasMany(DunningLog::class, 'campaign_id');
    }
}
