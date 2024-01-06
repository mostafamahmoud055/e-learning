<?php

namespace App\Models;

use App\Models\Course;
use App\Traits\FilesLink;
use App\Models\Assessment;
use App\Models\Scopes\StudentRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory, FilesLink;

    protected $hidden = [
        "username",
        "provider",
        "provider_id",
        "provider_token",
        "last_active_at",
        "created_at",
        "updated_at",
        "email_verified_at",
        "password",
        "remember_token",
        "code",
        "image"
    ];

    protected $table = 'users';
    protected $appends = ['photo'];

    protected static function booted(): void
    {
        static::addGlobalScope(new StudentRole);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'users_courses', 'user_id', 'course_id', 'id', 'id')
            ->using(UserCourse::class)->withPivot(['progress']);
    }

    public function assessments()
    {
        return $this->belongsToMany(Assessment::class, 'users_assessments', 'user_id', 'assessments_id', 'id', 'id')
            ->using(UserAssessment::class)->withPivot(['questions','total_degree']);
    }
    
}
