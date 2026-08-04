<?php

namespace App\Http\Controllers\GelAdmin\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SecurityController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('gel-admin.security.index', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }

    public function sessions()
    {
        $user = Auth::user();
        // Laravel doesn't track active sessions efficiently out of the box unless database session driver is used
        // Assuming database session driver:
        $sessions = [];
        if (config('session.driver') === 'database') {
            $sessions = DB::table('sessions')
                ->where('user_id', $user->id)
                ->orderBy('last_activity', 'desc')
                ->get();
        }

        // Login logs if we have them
        $loginLogs = DB::table('gel_login_logs')->where('user_id', $user->id)->orderBy('login_at', 'desc')->take(10)->get();

        return view('gel-admin.security.sessions', compact('sessions', 'loginLogs'));
    }

    public function revokeSession($id)
    {
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('id', $id)->where('user_id', Auth::id())->delete();
            return back()->with('success', 'Session révoquée avec succès.');
        }

        return back()->with('error', 'Le pilote de session ne permet pas cette action.');
    }
}
