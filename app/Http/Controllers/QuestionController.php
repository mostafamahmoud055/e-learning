<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Course;
use App\Models\Question;
use App\Models\Assessment;
use App\Traits\FileProcess;
use Illuminate\Http\Request;
use App\Traits\GeneralResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\QuestionCreateRequest;

class QuestionController extends Controller
{
    use GeneralResponse, FileProcess;
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
    public function store(QuestionCreateRequest $request)
    {
        $course = Course::find($request->course_id);
        Gate::authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        foreach ($request->questions as $question) {
            Question::create([
                'question' => $question,
                'assessment_id' => $request->assessment_id,
                'course_id' => $request->course_id,
            ]);
        }
        $assessment = Assessment::find($request->assessment_id);
        return $this->returnData("Question created successfully", $assessment->load('questions.resources'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            'question.name' => ['required', 'string', 'max:300', 'distinct'],
            'question.degree' => ['required', 'integer', 'between:0,100'],
            'question.options' => ['required', 'array', 'min:1'],
            'question.options.*.option' => ['required', 'string', 'max:100', 'distinct', Rule::unique(Answer::class)->where('question_id', $request->question_id)],
            'options.*.true' => ['required', 'in:0,1'],
            // 'file' => ['sometimes','required', 'array', 'min:1'],
            // 'file.*' => ['required', 'mimes:jpeg,jpg,png,gif,pdf,docx,xlsx,mp4,ogg,wmv,webm,mp3', 'max:1000000'],
            'assessment_id' => 'required|exists:assessments,id|integer',
        ]);
        $question = Question::find($id);
        if (!$question)
            return $this->returnError("question not found ", 404);

        $course = Course::find($question->course_id);
        $this->authorize('create-update-delete-assign-module-lesson-assessment-question', $course);

        $question->update($request->all());
        $assessment = Assessment::find($request->assessment_id);
        return $this->returnData("Question updated successfully", $assessment->load('questions.resources'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $question = Question::find($id);
        $course = Course::find($question->course_id);
        $this->authorize('create-module-lesson-assessment-question', $course);

        if (!$question)
            return $this->returnError("Question not found", 404);

        foreach ($question->resources as $resource) {
            $image = str_replace('http://' . $_SERVER['HTTP_HOST'] . '/images',  '', $resource->path);
            Storage::delete($image);
        }
        $question->resources()->where('resourceable_id', $question->id)->delete();

        $question = Question::destroy($id);

        if ($question)
            return $this->returnSuccessMessage('Question deleted successfully');
    }

    public function attachFileToQuestion(Request $request)
    {

        $course = Course::find($request->course_id);
        $this->authorize('create-module-lesson-assessment-question', $course);

        $question = Question::find($request->question_id);

        $request->validate([
            'file' => ['required', 'array', 'min:1'],
            'file.*' => ['required', 'mimes:jpeg,jpg,png,gif,pdf,docx,xlsx,mp4,ogg,wmv,webm,mp3', 'max:1000000'],
            'question_id' => 'required|exists:questions,id|integer',
            'course_id' => 'required|exists:courses,id|integer',
        ]);

        if ($path = $this->uploadFile($request, 'resources/' . $course->subject . '/' . $course->name)) {
            for ($i = 0; $i < count($path); $i++) {
                $data['file'][$i] = 'images/' . $path[$i];
            }
        }

        for ($i = 0; $i < count($request->file); $i++) {
            $question->resources()->create([
                'path' => $data['file'][$i],
                'type' => $request->file[$i]->getMimeType()
            ]);
        }
        $assessment = Assessment::find($question->assessment_id);
        return $this->returnData("Files attached to question successfully", $assessment->load('questions.resources'));
    }
}
