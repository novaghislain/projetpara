<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'client_id',
        'type', // financial_summary, tax_summary, project_profitability
        'parameters', // json {start_date, end_date}
        'file_path', // path to pdf/excel
        'generated_at',
        'status' // pending, completed, failed
    ];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }
}
