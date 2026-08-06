<?php

namespace App\Http\Controllers\GelInformaticien;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;

class MaintenanceController extends Controller
{
    /**
     * Affiche l'état des sauvegardes et la planification des maintenances
     */
    public function index()
    {
        $backups = AuditLog::whereIn('action', ['system_backup_success', 'system_backup_failed'])
                           ->orderBy('created_at', 'desc')
                           ->take(20)
                           ->get();
                           
        return view('gel-informaticien.maintenance.index', compact('backups'));
    }
}
