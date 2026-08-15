<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhLeaveRequest;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $leaves = RhLeaveRequest::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Rh/Leaves/Index',
            'props' => ['leaves' => $leaves]
        ]);
    }
}