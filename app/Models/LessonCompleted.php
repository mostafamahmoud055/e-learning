<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonCompleted extends Model
{
    use HasFactory;

    protected $table = 'lesson_completed';
    protected $fillable = ["user_id", "course_id", "lesson_id"];


    public function progress($course_id)
    {
        $userCourse = UserCourse::where('user_id', Auth::user()->id)
            ->where('course_id', $course_id)->first();

        $total_of_lessons = Lesson::where('course_id', $course_id)->get()->count();

        $completed_lessons = LessonCompleted::where('user_id', Auth::user()->id)
            ->where('course_id', $course_id)->get()->count();

        $progress = ($completed_lessons / $total_of_lessons) * 100 . '%';

        $userCourse->progress = $progress;
        $userCourse->save();
    }
}
