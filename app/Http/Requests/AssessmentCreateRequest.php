<?php

namespace App\Http\Requests;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Question;
use App\Rules\ModuleValidation;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AssessmentCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique(Assessment::class)->where('course_id', $this->input('course_id'))],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.name' => ['required', 'string', 'max:300', 'distinct'],
            'questions.*.degree' => ['required', 'integer','between:0,100'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.options.*.option' => ['required', 'string', 'max:100'],
            'questions.*.options.*.true' => ['required', 'in:0,1'],
            // 'question.*.file' => ['sometimes','required', 'array', 'min:1'],
            // 'question.*.file.*' => ['required', 'mimes:jpeg,jpg,png,gif,pdf,docx,xlsx,mp4,ogg,wmv,webm,mp3', 'max:1000000'],
            'course_id' => 'required|exists:courses,id|integer',
            'module_id' => ['nullable', 'exists:modules,id', 'integer', new ModuleValidation],
        ];
    }
}
