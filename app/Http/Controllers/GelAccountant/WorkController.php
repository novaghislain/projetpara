<?php

namespace App\Http\Controllers\GelAccountant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkController extends Controller
{
    public function index()
    {
        return view('gel-accountant.work.index');
    }
}
