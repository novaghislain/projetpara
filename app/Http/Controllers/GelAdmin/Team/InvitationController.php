<?php

namespace App\Http\Controllers\GelAdmin\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\GelAdmin\CabinetInvitation;

class InvitationController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        if ($type === 'cabinet') {
            $invitations = CabinetInvitation::where('cabinet_id', $cabinet->id)->get();
        } else {
            $invitations = collect([]);
        }
        // Load roles if spatie is used
        $roles = \Spatie\Permission\Models\Role::where('guard_name', 'web')->get();
        
        return view('gel-admin.team.invitations', compact('cabinet', 'invitations', 'roles'));
    }

    public function send(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id'
        ]);

        $token = Str::random(64);

        if ($type === 'cabinet') {
            CabinetInvitation::create([
                'cabinet_id' => $cabinet->id,
                'email' => $request->email,
                'role_id' => $request->role_id,
                'token' => $token,
                'expires_at' => now()->addDays(7),
            ]);
        }

        // MOCK: Send email here
        // Mail::to($request->email)->send(new CabinetInvitationMail($token));

        return back()->with('success', 'Invitation envoyée avec succès.');
    }

    public function cancel($id)
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        if ($type === 'cabinet') {
            $invitation = CabinetInvitation::where('cabinet_id', $cabinet->id)->findOrFail($id);
            $invitation->delete();
        }

        return back()->with('success', 'Invitation annulée.');
    }
}
