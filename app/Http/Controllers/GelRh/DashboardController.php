<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhPayslip;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'employes_actifs' => RhEmployee::where('status', 'actif')->count(),
            'conges_en_attente' => RhLeaveRequest::where('status', 'en_attente')->count(),
            'paies_a_valider' => RhPayslip::where('status', 'brouillon')->count(),
        ];

        return view('app', [
            'page' => 'Modules/Rh/Dashboard',
            'props' => ['stats' => $stats]
        ]);
    }
}