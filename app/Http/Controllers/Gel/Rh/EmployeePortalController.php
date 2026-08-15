<?php

namespace App\Http\Controllers\Gel\Rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rh\LeaveRequest;

class EmployeePortalController extends Controller
{
    public function requestLeave(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'employee_id' => 'required|integer',
            'leave_type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string'
        ]);

        $leave = LeaveRequest::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $leave,
            'message' => 'Demande de congés envoyée.'
        ]);
    }

    public function processLeaveRequest(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'manager_comment' => 'nullable|string'
        ]);

        $leave->update([
            'status' => $request->status,
            'manager_comment' => $request->manager_comment
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $leave,
            'message' => 'Demande traitée.'
        ]);
    }
}
