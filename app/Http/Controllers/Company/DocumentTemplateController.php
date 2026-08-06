<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentTemplateController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $templates = DocumentTemplate::where('client_id', Auth::user()->client_id)
            ->orWhereNull('client_id') // Allow system-wide templates
            ->latest()
            ->paginate(15);
            
        if ($request->wantsJson()) {
            return response()->json($templates);
        }

        return \Inertia\Inertia::render('Company/Settings/DocumentTemplates', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'file_path' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['client_id'] = Auth::user()->client_id;

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('document_templates', 'public');
        }

        $template = DocumentTemplate::create($data);

        return response()->json(['message' => 'Modèle de document créé avec succès', 'template' => $template]);
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        if ($documentTemplate->client_id !== Auth::user()->client_id) {
            abort(403);
        }
        $documentTemplate->delete();
        return response()->json(['message' => 'Modèle de document supprimé']);
    }
}
