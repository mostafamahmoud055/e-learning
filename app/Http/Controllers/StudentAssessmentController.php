<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use Illuminate\Http\Request;
use App\Models\StudentAssessment;
use App\Traits\GeneralResponse;
use Illuminate\Support\Facades\Auth;

class StudentAssessmentController extends Controller
{

    use GeneralResponse;

    public function __construct()
    {
        $this->middleware('jwtAuth');
    }

    public function create(Request $request)
    {
        $request->validate([
            'assessment_id' => ['required', 'exists:assessments,id', 'integer'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['required', 'exists:questions,id', 'integer'],
            'question.*.option' => ['required', 'string', 'max:300'],
            'total_degree' => ['required', 'integer', 'between:0,100'],
        ]);
        $assessment = Assessment::where('id', $request->assessment_id)->first();
        if (!$assessment) {
            return $this->returnError("Assessment not found in this course", 404);
        }

        $StudentAssessment = StudentAssessment::where('user_id', Auth::id())
            ->where('assessment_id', $request->assessment_id)->first();

        if ($StudentAssessment) {
            return $this->returnError("student completed this assessment before", 404);
        }
        $StudentAssessment = $assessment->students()->syncWithoutDetaching(
            [Auth::id() => [
                'assessment_id' => $request->assessment_id,
                'questions' => $request->questions,
                'total_degree' => $request->total_degree
            ]]
        );

        if ($StudentAssessment) {
            return $this->returnSuccessMessage('student completed this assessment successfully');
        }
    }
    public function show(string $id)
    {
        $StudentAssessment = StudentAssessment::where('user_id', Auth::id())
            ->where('assessment_id', $id)->first();

        if ($StudentAssessment) {
            return $this->returnData("assessment", $StudentAssessment);
        }
        return $this->returnError("student has not completed this assessment", 404);
    }
}
