<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    public function index()
    {
        // Fake financial data for demonstration
        $mrr = 45200;
        $arr = $mrr * 12;
        $unpaidInvoices = 8400;
        
        $history = [
            ['month' => 'Juin 2026', 'revenue' => 45200, 'expenses' => 28500],
            ['month' => 'Mai 2026', 'revenue' => 43000, 'expenses' => 28000],
            ['month' => 'Avril 2026', 'revenue' => 40500, 'expenses' => 27000],
            ['month' => 'Mars 2026', 'revenue' => 41000, 'expenses' => 25500],
        ];

        return view('gel-direction.finance.index', compact('mrr', 'arr', 'unpaidInvoices', 'history'));
    }
}
