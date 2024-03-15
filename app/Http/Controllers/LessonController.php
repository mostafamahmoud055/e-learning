<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use App\Traits\Trashing;
use App\Traits\FileProcess;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\LessonCreateRequest;
use App\Http\Requests\LessonUpdateRequest;
use App\Observers\LessonObserver;

class LessonController extends Controller
{
    use FileProcess, GeneralResponse, Trashing;
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
    public function store(LessonCreateRequest $request)
    {
        $course = Course::find($request->course_id);
        Gate::authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $data = $request->except('file');
        if ($path = $this->uploadFile($request, 'resources/' . $course->subject . '/' . $course->name)) {
            for ($i = 0; $i < count($path); $i++) {
                $data['file'][$i] = 'images/' . $path[$i];
            }
        }
        
        $lesson = Lesson::create($data);
        if ($lesson && isset($data['file'])) {
            for ($i = 0; $i < count($request->file); $i++) {
                $lesson->resources()->create([
                    'path' => $data['file'][$i],
                    'type' => $request->file[$i]->getMimeType()
                ]);
            }
        }
        return $this->returnData("Lesson created successfully ", $lesson->load('resources'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //     $lesson = Lesson::with('resources')->find($id);
        //     if (!$lesson) {
        //         return $this->returnError("Lesson not found ", 404);
        //     }
        //     $course = Course::with('modules')->find($lesson->course_id);
        //     $moduleName = $course->modules[0]->name;
        //     return $this->returnData("course subject: $course->subject / course name: $course->name / module name: $moduleName ", $lesson);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LessonUpdateRequest $request, string $id)
    {

        $lesson = Lesson::find($id);
        if (!$lesson)
            return $this->returnError("Lesson not found ", 404);

        $course = Course::with('modules')->find($lesson->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $moduleName = $course->modules[0]->name;

        $data = $request->except('file');

        if ($path = $this->uploadFile($request, 'resources/' . $course->subject . '/' . $course->name)) {
            for ($i = 0; $i < count($path); $i++) {
                $data['file'][$i] = 'images/' . $path[$i];
            }
        }
        
        if ($request->hours) {
            $lessonObserver = new LessonObserver;
            $lessonObserver->deleted($lesson);
        }

        $lesson->update($data);

        if ($request->hours) {
            $new_update = Lesson::find($id);
            $lessonObserver->created($new_update);
        }

        if ($lesson && isset($data['file'])) {
            for ($i = 0; $i < count($request->file); $i++) {
                $lesson->resources()->create([
                    'path' => $data['file'][$i],
                    'type' => $request->file[$i]->getMimeType()
                ]);
            }
        }
        return $this->returnData("course subject: $course->subject / course name: $course->name / module name: $moduleName Lesson updated successfully", $lesson->load('resources'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson)
            return $this->returnError('Lesson not found', 404);
        $course = Course::find($lesson->course_id);

        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);


        $lesson->delete();
        return $this->returnSuccessMessage('Lesson deleted successfully');
    }

    public function ResourceDelete(string $id)
    {
        $resource = Resource::find($id);

        if (!$resource)
            return $this->returnError("Resource not found", 404);
        Gate::authorize('delete-resource', $resource);

        $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/', '', $resource->path);

        Storage::delete($image);

        $dir = dirname($image);
        $resource->Delete();
        $this->deleteDir($dir);
        return $this->returnSuccessMessage("Resource deleted successfully");
    }

    public function trash()
    {
        return  $this->trashPush(Lesson::class);
    }

    public function restore(string $id)
    {
        return  $this->trashRestore($id, Lesson::class);
    }

    public function forceDelete(string $id)
    {
        return $this->trashForceDelete($id, Lesson::class);
    }
}
