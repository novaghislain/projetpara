<?php

namespace App\Models\Gel;

use Illuminate\Database\Eloquent\Model;

class MarketingBrief extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'description',
        'budget_estimation',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
