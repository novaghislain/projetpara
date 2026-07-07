<?php

namespace App\Http\Controllers\GelBusiness;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanqueController extends Controller
{
    public function index()
    {
        return view('gel-business.banque.index');
    }

    public function rapprochement()
    {
        return view('gel-business.banque.rapprochement');
    }
}
