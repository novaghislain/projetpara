<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ValidationController extends Controller
{
    public function index()
    {
        // 1. Tâches critiques nécessitant approbation (Mock ou réel si existant)
        $tasksToApprove = \DB::table('gel_tasks')
            ->where('statut', 'en_attente_validation')
            ->get();

        // 2. Déclarations fiscales à valider
        $declarationsToApprove = collect([
            (object)[
                'id' => 1,
                'client_nom' => 'TechInnov SARL',
                'type' => 'TVA',
                'periode' => 'Juillet ' . date('Y'),
                'montant' => 450000,
                'created_at' => now()->subDays(2),
            ],
            (object)[
                'id' => 2,
                'client_nom' => 'Africa Logistics',
                'type' => 'AIB',
                'periode' => 'Juillet ' . date('Y'),
                'montant' => 125000,
                'created_at' => now()->subDay(),
            ]
        ]);

        // 3. Demandes de congés (RH)
        $leaveRequests = \DB::table('rh_leave_requests')
            ->join('gel_salaries', 'rh_leave_requests.employee_id', '=', 'gel_salaries.id')
            ->select('rh_leave_requests.*', 'gel_salaries.nom', 'gel_salaries.prenom')
            ->where('rh_leave_requests.statut', 'pending')
            ->get();

        return view('gel-direction.validations.index', compact('tasksToApprove', 'declarationsToApprove', 'leaveRequests'));
    }

    public function approve(Request $request, $id)
    {
        $type = $request->input('type');
        
        if ($type === 'conge') {
            \DB::table('rh_leave_requests')->where('id', $id)->update(['statut' => 'approved']);
        } elseif ($type === 'tache') {
            \DB::table('gel_tasks')->where('id', $id)->update(['statut' => 'termine']);
        }
        
        return redirect()->back()->with('success', 'Élément validé avec succès.');
    }

    public function reject(Request $request, $id)
    {
        $type = $request->input('type');
        
        if ($type === 'conge') {
            \DB::table('rh_leave_requests')->where('id', $id)->update(['statut' => 'rejected']);
        } elseif ($type === 'tache') {
            \DB::table('gel_tasks')->where('id', $id)->update(['statut' => 'a_faire']);
        }

        return redirect()->back()->with('success', 'Élément rejeté.');
    }
}
