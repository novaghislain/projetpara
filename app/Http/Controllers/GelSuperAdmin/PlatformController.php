<?php

namespace App\Http\Controllers\GelSuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function config()
    {
        return view('gel-super-admin.platform.config');
    }

    public function stats()
    {
        return view('gel-super-admin.platform.stats');
    }

    public function support()
    {
        return view('gel-super-admin.platform.support');
    }

    public function audit()
    {
        return view('gel-super-admin.platform.audit');
    }
}
