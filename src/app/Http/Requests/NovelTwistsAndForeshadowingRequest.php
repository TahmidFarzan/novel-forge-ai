<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelTwistsAndForeshadowingRequest extends FormRequest
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
            'additional_information' => [
                'nullable',
                'string',
            ],

            'story_structure' => [
                'required',
            ],

            'characters' => [
                'required',
            ],

            'world_bible' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'story_structure.required'          => 'Story Structure is required.',
            'characters.required'               => 'Characters are required.',
            'world_bible.required'              => 'World Bible is required.',
            'additional_information.string'     => 'Additional Information must be a string.',
        ];
    }
}