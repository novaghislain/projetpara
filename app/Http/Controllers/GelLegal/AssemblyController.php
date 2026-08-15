<?php

namespace App\Http\Controllers\GelLegal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalAssembly;

class AssemblyController extends Controller
{
    public function index()
    {
        $assemblies = LegalAssembly::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Legal/Assemblees/Index',
            'props' => [
                'assemblies' => $assemblies
            ]
        ]);
    }
}