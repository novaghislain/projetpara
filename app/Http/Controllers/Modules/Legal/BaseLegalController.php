<?php

namespace App\Http\Controllers\Modules\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class BaseLegalController extends Controller
{
    protected function getClientId(Request $request): int
    {
        $clientId = $request->input('client_id') ?: Auth::user()?->client_id;
        if (!$clientId) {
            abort(403, 'Aucune entreprise associée.');
        }
        return (int) $clientId;
    }
}
