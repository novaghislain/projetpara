<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\UserClient;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class PoolController extends Controller
{
    /**
     * Affiche la liste du personnel GEL (Secrétaires et Comptables).
     */
    public function index()
    {
        // On récupère le personnel du pool
        $personnel = User::where('workspace_type', 'gel_pool')
                         ->withCount(['userClients as active_assignments_count' => function ($query) {
                             $query->where('is_active', true);
                         }])
                         ->get();

        return view('gel-super-admin.pool.index', compact('personnel'));
    }

    /**
     * Affiche les entreprises en attente d'affectation et les affectations actives.
     */
    public function assignments()
    {
        // Entreprises "Service Géré" sans secrétaire OU sans comptable (en attente)
        $waitingClients = Client::where('service_mode', 'service_gere')
                                ->where(function ($query) {
                                    $query->whereNull('assigned_secretary_id')
                                          ->orWhereNull('assigned_accountant_id');
                                })
                                ->get();

        // Entreprises "Service Géré" entièrement affectées
        $assignedClients = Client::where('service_mode', 'service_gere')
                                 ->whereNotNull('assigned_secretary_id')
                                 ->whereNotNull('assigned_accountant_id')
                                 ->with(['assignedSecretary', 'assignedAccountant'])
                                 ->get();

        // Personnel disponible
        $secretaries = User::where('workspace_type', 'gel_pool')
                           ->where(function($q) {
                               $q->where('role', 'secretaire')
                                 ->orWhere('role_secretaire', true);
                           })
                           ->get();

        $accountants = User::where('workspace_type', 'gel_pool')
                           ->where(function($q) {
                               $q->where('role', 'comptable')
                                 ->orWhere('role', 'chef_comptable');
                           })
                           ->get();

        return view('gel-super-admin.pool.assignments', compact('waitingClients', 'assignedClients', 'secretaries', 'accountants'));
    }

    /**
     * Traite l'affectation d'un personnel à une entreprise.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'secretary_id' => 'nullable|exists:users,id',
            'accountant_id' => 'nullable|exists:users,id',
        ]);

        $client = Client::findOrFail($request->client_id);
        
        DB::beginTransaction();
        try {
            if ($request->secretary_id) {
                $client->assigned_secretary_id = $request->secretary_id;
                $this->grantAccess($request->secretary_id, $client->id);
                $secretary = User::find($request->secretary_id);
                AuditLog::create([
                    'client_id' => $client->id,
                    'user_id' => auth()->id(),
                    'action' => 'affectation_personnel',
                    'description' => "Affectation du secrétaire {$secretary->name} {$secretary->prenom}",
                    'ip_address' => request()->ip(),
                ]);
            }

            if ($request->accountant_id) {
                $client->assigned_accountant_id = $request->accountant_id;
                $this->grantAccess($request->accountant_id, $client->id);
                $accountant = User::find($request->accountant_id);
                AuditLog::create([
                    'client_id' => $client->id,
                    'user_id' => auth()->id(),
                    'action' => 'affectation_personnel',
                    'description' => "Affectation du comptable {$accountant->name} {$accountant->prenom}",
                    'ip_address' => request()->ip(),
                ]);
            }

            $client->save();
            DB::commit();

            return back()->with('success', 'Affectation(s) enregistrée(s) avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'affectation : ' . $e->getMessage());
        }
    }

    /**
     * Donne accès au client pour l'utilisateur spécifié.
     */
    private function grantAccess($userId, $clientId)
    {
        UserClient::updateOrCreate(
            ['user_id' => $userId, 'client_id' => $clientId],
            ['is_active' => true]
        );
    }
}
