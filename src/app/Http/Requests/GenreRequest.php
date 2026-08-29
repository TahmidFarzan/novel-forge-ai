<?php

namespace App\Http\Requests;

use App\Models\Language;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GenreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            "name"         => ["required", "string", "max:200", Rule::unique('genres')->ignore($this->route('slug'), 'slug')],
            "brief"      => ["nullable"],
        ];
    }

    public function messages()
    {
        return [
            "name.required"       => "The name field is required.",
            "name.string"         => "The name must be a string.",
            "name.max"            => "The name may not be greater than 200 characters.",
        ];
    }
}
