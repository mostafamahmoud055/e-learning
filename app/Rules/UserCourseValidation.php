<?php

namespace App\Rules;

use App\Models\Course;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserCourseValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $Banned_students = [];
        $course = Course::where('id', $value)->select('grade', 'class')->first();
        
        foreach (request()->student_id as $id) {
            $students[] = User::where('id', $id)->select('first_name', 'last_name', 'grade', 'class')->first();
        }
        
        for ($i = 0; $i < count($students); $i++) {
            $check = false;
            for ($j = 0; $j < count($course->class); $j++) {
                if ([$course->grade, $course->class[$j]] == [$students[$i]->grade, $students[$i]->class]) {
                    $check = true;
                }
            }
            if (!$check) {
                $Banned_students[] = $students[$i];
            }
        }
        if (count($Banned_students) > 0) {
            foreach ($Banned_students as $student) {
                $fail("student $student->first_name $student->last_name is not at the same grade/class as this course");
            }
        }
    }
}
