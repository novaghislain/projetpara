<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $fillable = [
        'course_id',
        'employee_id',
        'status', // enrolled, in_progress, completed
        'progress_percentage',
        'completed_at'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
