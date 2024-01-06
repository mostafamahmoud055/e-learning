<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CourseCreateRequest extends FormRequest
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
                'required',
                Rule::unique('courses', 'name')->where('subject', $this->input('subject')), 'string', 'max:70'
            ],
            'subject' => ['required', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:600'],
            'photo' => ['sometimes','required', 'image', 'mimes:jpeg,jpg,png', 'max:10000'],
            'grade' => ['required', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            "class"    => ['required', 'array', 'min:1'],
            "class.*"  => ['required', 'in:A,B,C,D,E','distinct'],
            "target"    => ['required', 'array', 'min:1'],
            "target.*"  => ['required', 'string','distinct', 'min:3'],
        ];
    }
    // 'unique'    => ':attribute is already used'

    public function messages()
    {
        return [
            'unique' => 'course name and subject are already used'
        ];
    }
}
