<?php

namespace App\Models\Lms;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'content_url', // Lien vers une vidéo ou PDF
        'duration_minutes',
        'category', // rh, technique, legal, compliance
        'is_mandatory'
    ];

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }
}
