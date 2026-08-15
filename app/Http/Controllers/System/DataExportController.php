<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\System\DataExport;
use Illuminate\Support\Str;

class DataExportController extends Controller
{
    public function requestExport(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'module' => 'required|string', // all, crm, accounting, hr
            'format' => 'required|in:json,csv,sql'
        ]);

        $export = DataExport::create([
            'client_id' => $request->client_id,
            'requested_by' => $request->user()->id,
            'module' => $request->module,
            'format' => $request->format,
            'status' => 'processing'
        ]);

        // Simuler la création du fichier (qui serait fait par un Job en arrière-plan)
        $filename = 'export_' . $request->module . '_' . time() . '.' . $request->format;
        
        $export->update([
            'file_path' => 'exports/' . $filename,
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $export,
            'message' => 'L\'export de vos données a été généré avec succès.'
        ]);
    }
}
