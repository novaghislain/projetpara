<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class ExpenseReportLine extends Model
{
    protected $fillable = [
        'expense_report_id',
        'date',
        'category', // travel, food, accommodation, supplies
        'merchant',
        'amount',
        'tax_amount',
        'receipt_path', // chemin vers l'image uploadée
        'ocr_data' // données brutes JSON de l'IA Vision
    ];

    protected $casts = [
        'ocr_data' => 'array'
    ];

    public function report()
    {
        return $this->belongsTo(ExpenseReport::class, 'expense_report_id');
    }
}
