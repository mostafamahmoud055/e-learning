<?php

namespace App\Http\Requests;

use App\Rules\ModuleValidation;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LessonCreateRequest extends FormRequest
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
            'name' => [
                'required', 'string', 'max:70',
                Rule::unique('lessons', 'name')->where('course_id', request()->course_id)->where('module_id', request()->module_id),
            ],
            'description' => [
                'required', 'string', 'max:255'
            ],
            'hours' => ['required', 'date_format:H:i',],
            'file' => ['required', 'array', 'min:1'],
            'file.*' => ['required', 'mimes:jpeg,jpg,png,gif,pdf,docx,xlsx,mp4,ogg,wmv,webm,mp3', 'max:1000000'],
            'module_id' => ['required', 'exists:modules,id', 'integer', new ModuleValidation],
            'course_id' => 'required|exists:courses,id|integer',
        ];
    }
}
