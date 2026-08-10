<?php

namespace App\Http\Controllers\GelDirection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TeamSupervisionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Tous les employés du cabinet
        $query = User::where('cabinet_id', $user->cabinet_id)
                     ->where('id', '!=', $user->id); // On exclut le dirigeant actuel si besoin
                     
        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }
        
        $team = $query->paginate(15);
        
        return view('gel-direction.team.index', compact('team'));
    }
    
    public function show(User $user)
    {
        // Vérifier l'appartenance au cabinet
        $currentUser = Auth::user();
        if ($user->cabinet_id !== $currentUser->cabinet_id) {
            abort(403);
        }
        
        return view('gel-direction.team.show', compact('user'));
    }
}
