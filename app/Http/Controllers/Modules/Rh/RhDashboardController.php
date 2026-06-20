<?php

namespace App\Http\Controllers\Modules\Rh;

use App\Http\Controllers\Controller;
use App\Models\Rh\RhEmployee;
use App\Models\Rh\RhAlert;
use App\Models\Rh\RhContract;
use App\Models\Rh\RhLeaveRequest;
use App\Models\Rh\RhExpense;
use App\Models\Rh\RhPayroll;
use App\Models\Rh\RhTraining;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RhDashboardController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'rh-dashboard']);
    }

    public function stats(Request $request)
    {
        $clientId = $request->input('client_id') ?: Auth::user()?->client_id;

        $employees = RhEmployee::byClient($clientId);
        $contracts = RhContract::whereIn('employee_id', $employees->pluck('id'));
        $leaves = RhLeaveRequest::whereIn('employee_id', $employees->pluck('id'));
        $expenses = RhExpense::whereIn('employee_id', $employees->pluck('id'));
        $payrolls = RhPayroll::whereIn('employee_id', $employees->pluck('id'));
        $trainings = RhTraining::whereIn('employee_id', $employees->pluck('id'));

        return response()->json([
            'total_employees' => $employees->count(),
            'active_employees' => $employees->where('status', 'actif')->count(),
            'total_contracts' => $contracts->count(),
            'active_contracts' => $contracts->where('statut', 'actif')->count(),
            'pending_leaves' => $leaves->where('statut', 'pending')->count(),
            'pending_expenses' => $expenses->where('statut', 'pending')->count(),
            'pending_payrolls' => $payrolls->where('statut', 'brouillon')->count(),
            'upcoming_trainings' => $trainings->where('statut', 'planifie')->count(),
            'active_alerts' => RhAlert::byClient($clientId)->actives()->count(),
            'recent_employees' => $employees->latest()->take(5)->get()->map(fn($e) => [
                'id' => $e->id,
                'nom' => $e->nom,
                'prenom' => $e->prenom,
                'poste' => $e->poste,
                'status' => $e->status,
                'photo' => $e->photo,
            ]),
            'pending_leaves_list' => $leaves->where('statut', 'pending')->latest()->take(5)->get()->map(fn($l) => [
                'id' => $l->id,
                'employee' => $l->employee?->full_name,
                'type' => $l->type,
                'date_debut' => $l->date_debut?->format('Y-m-d'),
                'date_fin' => $l->date_fin?->format('Y-m-d'),
            ]),
            'pending_expenses_list' => $expenses->where('statut', 'pending')->latest()->take(5)->get()->map(fn($e) => [
                'id' => $e->id,
                'employee' => $e->employee?->full_name,
                'categorie' => $e->categorie,
                'montant' => $e->montant,
            ]),
            'recent_alerts' => RhAlert::byClient($clientId)->actives()->latest()->take(5)->get()->map(fn($a) => [
                'id' => $a->id,
                'titre' => $a->titre,
                'type' => $a->type,
                'date_echeance' => $a->date_echeance?->format('Y-m-d'),
            ]),
        ]);
    }
}
