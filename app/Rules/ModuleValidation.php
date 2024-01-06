<?php

namespace App\Rules;

use Closure;
use App\Models\Module;
use Illuminate\Contracts\Validation\ValidationRule;

class ModuleValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $exist = Module::where('id', $value)
            ->where('course_id', request()->course_id)
            ->first();
        if (!$exist) {
            $fail('This :attribute is not related to this course');
        }
    }
}
