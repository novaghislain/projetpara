<?php

namespace App\Http\Controllers\GelAdmin\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TeamController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        $foreignKey = $type . '_id';
        
        $teamMembers = User::where($foreignKey, $cabinet->id)->get();
        
        return view('gel-admin.team.index', compact('cabinet', 'teamMembers'));
    }

    public function suspend(Request $request, $id)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        $foreignKey = $type . '_id';
        
        $member = User::where($foreignKey, $cabinet->id)->findOrFail($id);

        if ($member->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas suspendre votre propre compte.');
        }

        $member->is_suspended = true;
        $member->suspended_at = now();
        $member->suspended_reason = 'Suspendu par l\'administrateur';
        $member->save();

        return back()->with('success', 'Membre suspendu.');
    }

    public function revoke(Request $request, $id)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        $foreignKey = $type . '_id';
        
        $member = User::where($foreignKey, $cabinet->id)->findOrFail($id);

        if ($member->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas révoquer votre propre compte.');
        }

        // Dissocier du cabinet ou archiver
        $member->is_active = false;
        $member->save();

        return back()->with('success', 'Accès membre révoqué.');
    }
}
