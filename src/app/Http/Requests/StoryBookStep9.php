<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoryBookStep9 extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'foundation' => [
                'required',
            ],

            'characters' => [
                'required',
            ],

            'world_bible' => [
                'required',
            ],

            'timeline' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'foundation.required'               => 'Foundation is required.',
            'characters.required'               => 'Characters are required.',
            'world_bible.required'              => 'World Bible is required.',
            'timeline.required'                 => 'Timeline is required.',
        ];
    }
}