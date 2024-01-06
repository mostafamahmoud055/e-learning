<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LessonUpdateRequest extends FormRequest
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
        $id = request()->route()->parameter('Lesson');
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:70',
                Rule::unique('lessons', 'name')->where('course_id', request()->course_id)->where('module_id', request()->module_id)->ignore($id)
            ],
            'description' => [
                'sometimes', 'required', 'string', 'max:255'
            ],
            'file' => ['sometimes', 'required', 'array', 'min:1'],
            'file.*' => ['sometimes', 'required', 'mimes:jpeg,jpg,png,gif,pdf,docx,xlsx,mp4,ogg,wmv,webm,mpeg,mp3', 'max:1000000'],
            'course_id' => 'required|exists:courses,id|integer',
            'module_id' => 'required|exists:modules,id|integer',
            'hours' => ['sometimes','required', 'date_format:H:i',],
        ];
    }
}
