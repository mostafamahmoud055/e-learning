<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Question;
use App\Models\Assessment;
use Illuminate\Http\Request;
use App\Rules\ModuleValidation;
use App\Traits\GeneralResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AssessmentCreateRequest;

class AssessmentController extends Controller
{
    use GeneralResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssessmentCreateRequest $request)
    {

        $course = Course::find($request->course_id);
        Gate::authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $assessment = Assessment::where('course_id', $request->course_id)
            ->where('module_id', $request->module_id)->first();

        if ($assessment) {
            return $this->returnError("this course or module has assessment already", 404);
        }

        DB::beginTransaction();
        try {
            $assessment = Assessment::create([
                'name' => $request->name,
                'course_id' => $request->course_id,
                'module_id' => $request->module_id,
            ]);
            foreach ($request->questions as $question) {
                Question::create([
                    'question' => $question,
                    'assessment_id' => $assessment->id,
                    'course_id' => $request->course_id,
                ]);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        return $this->returnData("Assessment created successfully", $assessment->load('questions.resources'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $assessment = Assessment::with('questions')->find($id);

        // if (!$assessment)
        //     return $this->returnError("Assessment not found", 404);
        // // $this->authorize('view', $course);
        // $assessment = Assessment::with('questions.resources')->get();
        // foreach ($assessment as $assessment) {
        //     foreach ($assessment->questions as $questions) {
        //         $questions->resources;
        //     }
        // }
        // // $course->modules = $modules;
        // if ($assessment)
        //     return $this->returnData("$assessment", $assessment);

        // return $this->returnError("Assessment not found", 404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
                Rule::unique(Assessment::class)->where('course_id', $request->course_id)->ignore($id),
            ],
            'course_id' => 'required|exists:courses,id|integer',
            'module_id' => 'sometimes|required|exists:modules,id|integer',
        ]);

        $course = Course::find($request->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $assessment = Assessment::find($id);
        if (!$assessment)
            return $this->returnError("Assessment not found", 404);

        $assessment->update($request->all());

        return $this->returnData("Assessment updated successfully", $assessment->load('questions.resources'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $assessment = Assessment::find($id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $assessment->course_id);

        $assessment = Assessment::find($id);
        if (!$assessment)
            return $this->returnError("Assessment not found", 404);

        foreach ($assessment->questions as $question) {
            foreach ($question->resources as $resource) {
                $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images/',  '', $resource->path);
                Storage::delete($image);
            }
            $question->resources()->where('resourceable_id', $question->id)->delete();
        }
        $assessment = Assessment::destroy($id);

        if ($assessment)
            return $this->returnSuccessMessage('Assessment deleted successfully');
    }
}
