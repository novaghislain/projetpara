<?php
namespace App\Http\Controllers\GelRh;
use App\Http\Controllers\Controller;
use App\Models\Rh\RhPayslip;

class PayrollController extends Controller
{
    public function index()
    {
        $payslips = RhPayslip::latest('created_at')->paginate(15);
        return view('app', [
            'page' => 'Modules/Rh/Payroll/Index',
            'props' => ['payslips' => $payslips]
        ]);
    }
}