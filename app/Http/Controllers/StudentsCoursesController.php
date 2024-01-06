<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserCourse;
use App\Models\LessonCompleted;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use App\Rules\UserCourseValidation;
use Illuminate\Support\Facades\Auth;

class StudentsCoursesController extends Controller
{
    use GeneralResponse;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('jwtAuth');
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id', 'integer'], //, new UserCourseValidation (admin)
            "student_id"    => ['required', 'array', 'min:1'],
            "student_id.*"  => ['required', 'exists:users,id', 'integer'],
        ]);
        $course = Course::find($request->course_id);

        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $assigned = $course->students()->syncWithoutDetaching($request->student_id);

        if ($assigned) {
            return $this->returnSuccessMessage('students are assigned to this course successfully');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lesson = Lesson::where('course_id', $request->course_id)
            ->where('id', $id)->first();
        if (!$lesson) {
            return $this->returnError("Lesson not found in this course", 404);
        }

        $userCourse = UserCourse::where('user_id', Auth::id())
            ->where('course_id', $request->course_id)->first();


        if (!$userCourse) {
            return $this->returnError("student not assigned to this course", 404);
        }
        $next_id = Lesson::where('id', '>', $id)->min('id');

        $LessonCompleted = LessonCompleted::insert([
            [
                'user_id' => Auth::id(),
                'course_id' => $request->course_id,
                'lesson_id' => $id,
                'status' => "completed",
            ], [
                'user_id' => Auth::id(),
                'course_id' => $request->course_id,
                'lesson_id' => $next_id,
                'status' => "inprogress",
            ]
        ]);

        if ($LessonCompleted) {
            $Completed = new LessonCompleted();
            $Completed->progress($request->course_id);
            return $this->returnSuccessMessage('student completed this lesson successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // $lesson = Lesson::where('course_id', $request->course_id)
        //     ->where('id', $id)->first();
        // if (!$lesson) {
        //     return $this->returnError("Lesson not found in this course", 404);
        // }

        // $userCourse = UserCourse::where('user_id', Auth::id())
        //     ->where('course_id', $request->course_id)->first();
        // if (!$userCourse) {
        //     return $this->returnError("student not assigned to this course", 404);
        // }

        // $LessonCompleted = LessonCompleted::where('user_id', Auth::id())
        //     ->where('course_id', $request->course_id)
        //     ->where('lesson_id', $id)->delete();

        // if ($LessonCompleted) {
        //     $Completed = new LessonCompleted();
        //     $Completed->progress($request->course_id);
        //     return $this->returnSuccessMessage('The student did not complete this lesson');
        // }
    }

    public function removeStudentsFromCourse(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id', 'integer'],
            "student_id"    => ['required', 'array', 'min:1'],
            "student_id.*"  => ['required', 'exists:users,id', 'integer'],
        ]);
        $course = Course::find($request->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $unassigned = $course->students()->detach($request->student_id);

        if ($unassigned) {
            return $this->returnSuccessMessage('students are removed from this course successfully');
        }
    }
}
