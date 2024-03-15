<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Lesson;

class LessonObserver
{
    /**
     * Handle the Lesson "created" event.
     */
    public function created(Lesson $lesson): void
    {

        $course = Course::find($lesson->course_id);

        $lesson_time = explode(':', $lesson->hours);
        $course_time = explode(':', $course->hours);

        $hours = intval($course_time[0]) + intval($lesson_time[0]);
        if ((intval($course_time[1] ?? 0) + intval($lesson_time[1] ?? 0)) >= 60) {
            $minutes = intval($course_time[1]) + intval($lesson_time[1]);
            $minutes -= 60;
            $hours++;
        } else {
            $minutes = intval($course_time[1] ?? 0) + intval($lesson_time[1] ?? 0);
        }
        $course->hours = $hours . ":" . $minutes;
        $course->save();
    }

    /**
     * Handle the Lesson "updated" event.
     */
    public function updating(Lesson $lesson): void
    {
        //
    }

    /**
     * Handle the Lesson "deleted" event.
     */
    public function deleted(Lesson $lesson): void
    {
        $course = Course::find($lesson->course_id);

        $lesson_time = explode(':', $lesson->hours);
        $course_time = explode(':', $course->hours);

        $hours = intval($course_time[0]) - intval($lesson_time[0]);
        if (intval($course_time[1] ?? 0) - intval($lesson_time[1] ?? 0) < 0) {
            $minutes = abs(intval($course_time[1]) - intval($lesson_time[1]));
            $minutes -= 60;
            $hours--;
        } else {
            $minutes = intval($course_time[1] ?? 0) - intval($lesson_time[1] ?? 0);
        }
        $course->hours = $hours . ":" . abs($minutes);
        $course->save();
    }

    /**
     * Handle the Lesson "restored" event.
     */
    public function restored(Lesson $lesson): void
    {
        $this->created($lesson);
    }

    /**
     * Handle the Lesson "force deleted" event.
     */
    public function forceDeleted(Lesson $lesson): void
    {
        //
    }
}