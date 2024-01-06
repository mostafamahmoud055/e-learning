<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
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
        $id = request()->route()->parameter('Course');
        return [
            'name' => [
                'sometimes',
                Rule::unique('courses', 'name')->where('subject', $this->input('subject'))->ignore($id), 'string', 'max:70',
                'required', 'string', 'max:20'
            ],
            'subject' => ['sometimes', 'required', 'string', 'max:20'],
            'description' => ['sometimes', 'required', 'string', 'max:600'],
            'photo' => ['sometimes', 'required', 'image', 'mimes:jpeg,jpg,png', 'max:10000'],
            'grade' => ['sometimes', 'required', 'in:1,2,3,4,5,6,7,8,9,10,11,12'],
            "class"    => ['sometimes','required', 'array', 'min:1'],
            "class.*"  => ['required', 'in:A,B,C,D,E', 'distinct'],
            "target"    => ['sometimes', 'required', 'array', 'min:1'],
            "target.*"  => ['sometimes', 'required', 'string', 'distinct', 'min:3'],
            'active' => ['sometimes', 'required', 'in:0,1'],
            'rate' => ['sometimes', 'required', 'in:1,2,3,4,5'],
        ];
    }

    public function messages()
    {
        return [
            'unique'   => 'course name and subject are already used'
        ];
    }
}
