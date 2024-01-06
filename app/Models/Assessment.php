<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Question;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;

    public $fillable = ['name', 'course_id', 'module_id'];

    protected $hidden = [
        "created_at",
        "updated_at"
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'students_assessments', 'assessment_id', 'user_id', 'id', 'id')
            ->using(StudentAssessment::class)->withPivot(['questions','total_degree']);
    }
}
