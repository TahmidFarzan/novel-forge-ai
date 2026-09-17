<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelScenePlannerRequest extends FormRequest
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

            'twists_and_foreshadowing' => [
                'required',
            ],

            'locations' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'story_structure.required'              => 'Story Structure is required.',
            'twists_and_foreshadowing.required'     => 'Twists and Foreshadowing is required.',
            'locations.required'                    => 'Locations are required.',
            'additional_information.string'         => 'Additional Information must be a string.',
        ];
    }
}