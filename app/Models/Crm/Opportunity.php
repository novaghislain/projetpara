<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    protected $fillable = [
        'lead_id',
        'title',
        'expected_revenue',
        'probability', // pourcentage de chance de succès
        'expected_closing_date',
        'stage' // new, qualified, proposition, won, lost
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
