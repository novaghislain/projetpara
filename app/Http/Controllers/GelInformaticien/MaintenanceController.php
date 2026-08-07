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
                           
        $windows = \App\Models\Gel\ItMaintenanceWindow::latest()->take(5)->get();
                           
        return view('gel-informaticien.maintenance.index', compact('backups', 'windows'));
    }

    public function storeWindow(Request $request)
    {
        $request->validate([
            'starts_at' => 'required|date',
            'duration' => 'required|integer|min:1|max:24',
            'message' => 'required|string'
        ]);

        $starts = \Carbon\Carbon::parse($request->starts_at);
        $ends = $starts->copy()->addHours((int) $request->duration);

        \App\Models\Gel\ItMaintenanceWindow::create([
            'starts_at' => $starts,
            'ends_at' => $ends,
            'message' => $request->message,
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'La fenêtre de maintenance a bien été programmée.');
    }
}
