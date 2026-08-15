<?php

namespace App\Http\Controllers\Gel\Rh;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lms\Course;
use App\Models\Lms\CourseEnrollment;

class LmsController extends Controller
{
    public function createCourse(Request $request)
    {
        $request->validate([
            'client_id' => 'required|uuid',
            'title' => 'required|string',
            'duration_minutes' => 'required|integer',
            'category' => 'required|string',
        ]);

        $course = Course::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $course,
            'message' => 'Formation créée avec succès.'
        ]);
    }

    public function enrollEmployee(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'employee_id' => 'required|integer'
        ]);

        $enrollment = CourseEnrollment::create([
            'course_id' => $request->course_id,
            'employee_id' => $request->employee_id,
            'status' => 'enrolled'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $enrollment,
            'message' => 'Employé inscrit à la formation.'
        ]);
    }

    public function updateProgress(Request $request, $enrollmentId)
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);

        $request->validate([
            'progress_percentage' => 'required|integer|min:0|max:100'
        ]);

        $status = $enrollment->status;
        $completedAt = $enrollment->completed_at;

        if ($request->progress_percentage == 100) {
            $status = 'completed';
            $completedAt = now();
        } elseif ($request->progress_percentage > 0) {
            $status = 'in_progress';
        }

        $enrollment->update([
            'progress_percentage' => $request->progress_percentage,
            'status' => $status,
            'completed_at' => $completedAt
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $enrollment,
            'message' => 'Progression mise à jour.'
        ]);
    }
}
