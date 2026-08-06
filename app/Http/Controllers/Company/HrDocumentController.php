<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\HrDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HrDocumentController extends Controller
{
    public function index()
    {
        $documents = HrDocument::where('client_id', Auth::user()->client_id)
            ->with(['user', 'employee'])
            ->latest()
            ->paginate(15);
            
        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:rh_employees,id',
            'type' => 'required|string|in:attestation_travail,certificat_travail,contrat,autre',
            'document_name' => 'required|string|max:255',
            'file_path' => 'nullable|string',
            'status' => 'required|string|in:draft,issued,signed',
        ]);

        $data['user_id'] = Auth::id();
        $data['client_id'] = Auth::user()->client_id;

        // Simulate file upload logic if a file was provided
        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('hr_documents', 'public');
        }

        $document = HrDocument::create($data);

        return response()->json(['message' => 'Document RH créé avec succès', 'document' => $document]);
    }

    public function destroy(HrDocument $hrDocument)
    {
        if ($hrDocument->client_id !== Auth::user()->client_id) {
            abort(403);
        }
        $hrDocument->delete();
        return response()->json(['message' => 'Document RH supprimé']);
    }
}
