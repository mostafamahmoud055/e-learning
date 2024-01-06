<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
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
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('modules', 'name')->where('course_id', $request->course_id)
            ],
            'course_id' => 'required|exists:courses,id|integer',
        ]);
        $course = Course::find($request->course_id);
        Gate::authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $module = Module::create($request->all());
        return $this->returnData("module created successfully ", $module);
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
        $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('modules', 'name')->where('course_id', $request->course_id)
            ],
        ]);

        $module = Module::find($id);
        $course = Course::find($module->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        if (!$module)
            return $this->returnError("Module not found", 404);

        $module->update([
            'name' => $request->name
        ]);

        return $this->returnSuccessMessage('Module updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $module = Module::find($id);
        if (!$module)
            return $this->returnError("Module not found", 404);

        $course = Course::find($module->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        foreach ($module->lessons as $lesson) {
            foreach ($lesson->resources as $resource) {
                $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/', '', $resource->path);
                Storage::delete($image);
            }
            $lesson->resources()->where('resourceable_id', $lesson->id)->delete();
        }
        $module = Module::destroy($id);

        if ($module)
            return $this->returnSuccessMessage('Module deleted successfully');
    }
}
