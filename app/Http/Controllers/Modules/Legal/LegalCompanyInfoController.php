<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCompanyInfo;
use Illuminate\Http\Request;

class LegalCompanyInfoController extends Controller
{
    public function show(Request $request)
    {
        if (!$request->expectsJson()) {
            return view('app', ['page' => 'legal-societe']);
        }
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);
        $info = LegalCompanyInfo::byClient($clientId)->first();

        return response()->json($info);
    }

    public function update(Request $request)
    {
        $clientId = $request->get('client_id', auth()->user()->client_id ?? 0);

        $info = LegalCompanyInfo::byClient($clientId)->firstOrNew(['client_id' => $clientId]);
        $info->fill($request->all());
        $info->save();

        return response()->json(['success' => true, 'data' => $info]);
    }
}
