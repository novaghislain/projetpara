<?php

namespace App\Http\Controllers\GelClient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentsController extends Controller
{
    public function index()
    {
        $clientId = session('active_client_id');
        if (!$clientId) return redirect()->route('dashboard');

        $documents = DB::table('dae_documents')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('gel-client.documents.index', compact('documents'));
    }

    public function upload(Request $request)
    {
        // Logique d'upload d'un document pour transmission au cabinet
        return redirect()->route('gel-client.documents.index')->with('success', 'Document transmis au cabinet avec succès.');
    }
}
