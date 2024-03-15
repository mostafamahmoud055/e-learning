<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Student;
use App\Traits\Trashing;
use App\Models\Assessment;
use App\Traits\FileProcess;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\CourseCreateRequest;
use App\Http\Requests\CourseUpdateRequest;

class CourseController extends Controller
{
    use GeneralResponse, FileProcess, Trashing;

    public function __construct()
    {
        $this->middleware('jwtAuth')->except('index', 'AllCourses');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::select('*')->author()->orderBy('rate', 'desc')->take(10)->get();
        // $courses = Course::paginate();
        return $this->returnData("courses", $courses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseCreateRequest $request)
    {
        $allStudent = [];
        $this->authorize('create', Course::class);
        $data = $request->except('photo');
        $data['user_id'] = Auth::id();
        if ($path = $this->uploadFile($request, 'resources/' . $request->subject . '/' . $request->name)) {
            $data['image'] = 'images/' . $path;
        }
        // return($request->all());
        $course = Course::create($data);
        $students =  DB::table('users')
            ->select('id')
            ->where('grade', $request->grade)
            ->whereIn('class', $request->class)
            ->get();
        for ($i = 0; $i < count($students); $i++) {
            $allStudent[] = $students[$i]->id;
        }

        $course->students()->syncWithoutDetaching($allStudent);

        return $this->returnData("course created successfully ", $course);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::author()->with('Students')->find($id);
        $this->authorize('view', $course);
        $modules = Module::where('course_id', $course->id)->with('lessons.LessonCompleted')->get();
        foreach ($modules as $module) {
            foreach ($module->lessons as $lesson) {
                $lesson->resources;
            }
        }
        
        if ($course) {
            $course->modules = $modules->load('assessments.questions.resources');
            $assessment = Assessment::with('questions.resources')->where('course_id', $id)
                ->where('module_id', null)->first();
            $course->assessment = $assessment;
            return $this->returnData("$course->name course", $course);
        }

        return $this->returnError("Course not found", 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseUpdateRequest $request, string $id)
    {
        $course = Course::find($id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        if (!$course)
            return $this->returnError("Course not found", 404);

        $old_image = $course->image;
        $data = $request->except('photo');

        if (isset($request->subject)) {
            $subject = $request->subject;
        } else {
            $subject = $course->subject;
        }
        if (isset($request->name)) {
            $name = $request->name;
        } else {
            $name = $course->name;
        }

        if ($path = $this->uploadFile($request, 'resources/' . $subject . '/' . $name)) {
            $data['image'] = 'images/' . $path;
            $dir = dirname($course->image);
        }
        if (isset($request->grade) || isset($request->class)) {
            $classes_update = array_diff($request->class ?? [], $course->class);
            $classes_ignore = array_values(array_diff($request->class, $classes_update));
            $array_merge = array_merge($classes_ignore, $classes_update);
            $classes_ignore = array_values(array_diff($course->class, $array_merge));

            $course->update($data);

            $new_student = Student::query()->where('grade', $request->grade ?? $course->grade)
                ->when($classes_update ?? false, function ($query, $classes_update) {
                    $query->whereIn('class', $classes_update)
                        ->select('id');
                })->get();
            if ($new_student) {
                foreach ($new_student  as $student) {
                    $added_students[] = $student->id;
                }
                $course->students()->syncWithoutDetaching($added_students);
            }

            $old_student = Student::query()->where('grade', $request->grade ?? $course->grade)
                ->when($classes_ignore ?? false, function ($query, $classes_ignore) {
                    $query->whereIn('class', $classes_ignore)
                        ->select('id');
                })->get();

            if ($old_student) {
                foreach ($old_student  as $student) {
                    $removed_students[] = $student->id;
                }
                $course->students()->detach($removed_students);
            }
        }

        if ($old_image && isset($data['image'])) {
            $old_image = str_replace('images/', '', $old_image);
            if ($old_image != "noImg.jpg") {
                Storage::delete($old_image);
            }
            $this->deleteDir($dir);
        }
        return $this->returnSuccessMessage('Course updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $course = Course::find($id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        if (!$course)
            return $this->returnError('Course not found', 404);

        $course->delete();
        return $this->returnSuccessMessage('Course deleted successfully');
    }

    public function trash()
    {
        return  $this->trashPush(Course::class);
    }

    public function restore(string $id)
    {
        return  $this->trashRestore($id, Course::class);
    }

    public function forceDelete(string $id)
    {
        $course =  Course::onlyTrashed()->find($id);

        if (!$course)
            return $this->returnError('Course not found', 404);

        foreach ($course->lessons as $lesson) {
            foreach ($lesson->resources as $resource) {
                $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/',  '', $resource->path);
                Storage::delete($image);
            }
            $lesson->resources()->where('resourceable_id', $lesson->id)->delete();
        }
        return $this->trashForceDelete($course, Course::class);
    }

    public function AllCourses()
    {
        $courses = Course::author()->paginate();
        return $this->returnData("courses", $courses);
    }
}
