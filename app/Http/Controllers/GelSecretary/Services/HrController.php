<?php

namespace App\Http\Controllers\GelSecretary\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HrController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'leaves' => \App\Models\Gel\Leave::with('client')->orderBy('start_date', 'desc')->get(),
                'documents' => \App\Models\Gel\HrDocument::with('client')->orderBy('created_at', 'desc')->get()
            ]);
        }
        return view('gel-secretary.services.hr');
    }
}
