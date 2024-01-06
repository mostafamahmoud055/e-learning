<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Assessment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;
    public $fillable = ['name', 'course_id'];
    public $hidden = ['created_at', 'updated_at', 'course_id', 'photo'];

    public function courses()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
    public function assessments()
    {
        return $this->hasOne(Assessment::class);
    }
}
