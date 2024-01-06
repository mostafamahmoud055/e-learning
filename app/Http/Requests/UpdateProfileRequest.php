<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => ['sometimes','required', 'string', 'max:10'],
            'last_name' => ['sometimes','required', 'string', 'max:10'],
            'phone_number' => ['sometimes','required', 'regex:/^01[0125][0-9]{8}$/', 'max:11'],
            'photo' => ['sometimes','required', 'image', 'mimes:jpeg,jpg,png','max:10000'],
        ];
    }
}
