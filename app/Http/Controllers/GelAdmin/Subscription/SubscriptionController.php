<?php

namespace App\Http\Controllers\GelAdmin\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GelAdmin\CabinetSubscriptionInvoice;

class SubscriptionController extends Controller
{
    use \App\Http\Controllers\GelAdmin\Traits\HasAdminEntity;

    public function index()
    {
        $cabinet = $this->getAdminEntity();
        return view('gel-admin.subscription.index', compact('cabinet'));
    }

    public function changePlan(Request $request)
    {
        $cabinet = $this->getAdminEntity();
        $request->validate([
            'plan_id' => 'required|string',
        ]);

        // Logic to update plan
        // This is a stub for plan upgrading logic, normally would go to payment first.

        return redirect()->route('gel-admin.subscription.payment.show', ['plan_id' => $request->plan_id]);
    }

    public function invoices()
    {
        $cabinet = $this->getAdminEntity();
        $type = $this->getAdminEntityType();
        
        if ($type === 'cabinet') {
            $invoices = CabinetSubscriptionInvoice::where('cabinet_id', $cabinet->id)->orderBy('created_at', 'desc')->get();
        } else {
            $invoices = collect([]);
        }
        
        return view('gel-admin.subscription.invoices', compact('cabinet', 'invoices'));
    }
}
