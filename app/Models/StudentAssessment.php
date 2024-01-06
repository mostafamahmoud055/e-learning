<?php

namespace App\Models;

use App\Models\Student;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAssessment extends Pivot
{
    use HasFactory;

    protected $table = 'students_assessments';
    public $incrementing = true; // by default in pivot is false
    protected $hidden = ['created_at','updated_at'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    protected function questions(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value),
            set: fn ($value) => json_encode($value),
        );
    }
}
