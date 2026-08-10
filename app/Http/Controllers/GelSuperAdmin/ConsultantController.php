<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Gel\Entreprise;
use App\Models\Gel\ConsultantMission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ConsultantController extends Controller
{
    public function index()
    {
        $consultants = User::where('account_type', 'consultant')->orWhere('role', 'consultant')->get();
        $entreprises = Entreprise::all();
        $missions = ConsultantMission::with(['consultant', 'entreprise'])->latest()->get();

        return view('gel-super-admin.consultants.index', compact('consultants', 'entreprises', 'missions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'specialty' => 'nullable|string'
        ]);

        $password = Str::random(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'account_type' => 'consultant',
            'role' => 'consultant',
            'is_active' => true,
            'fonction' => $request->specialty // Stocké dans la fonction pour simplicité
        ]);

        $user->assignRole('consultant');

        return back()->with('success', 'Consultant créé avec succès. Mot de passe temporaire : ' . $password);
    }

    public function assign(Request $request, $id)
    {
        $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'title' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        ConsultantMission::create([
            'consultant_id' => $id,
            'entreprise_id' => $request->entreprise_id,
            'title' => $request->title,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'en_cours',
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Consultant affecté avec succès à l\'entreprise.');
    }
}
