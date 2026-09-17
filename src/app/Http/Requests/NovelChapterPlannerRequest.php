<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelChapterPlannerRequest extends FormRequest
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

            'scene_plans' => [
                'required',
            ],

            'story_structure' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'scene_plans.required'              => 'Scene Plans are required.',
            'story_structure.required'          => 'Story Structure is required.',
            'additional_information.string'     => 'Additional Information must be a string.',
        ];
    }
}