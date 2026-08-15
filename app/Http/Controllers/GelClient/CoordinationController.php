<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoordinationController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id');
        if (!$clientId) return redirect()->route('dashboard');

        $messages = DB::table('dae_messages')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('gel-client.coordination.index', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $clientId = session('active_client_id');
        if (!$clientId) return redirect()->back();

        $request->validate([
            'content' => 'required|string',
        ]);

        DB::table('dae_messages')->insert([
            'client_id' => $clientId,
            'expediteur' => 'client',
            'destinataire' => 'cabinet',
            'content' => $request->content,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->route('gel-client.coordination.index')->with('success', 'Message envoyé au cabinet.');
    }
}
