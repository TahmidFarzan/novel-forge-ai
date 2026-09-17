<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelCompleteNovelRequest extends FormRequest
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

            'foundation' => [
                'required',
            ],

            'characters' => [
                'required',
            ],

            'world_bible' => [
                'required',
            ],

            'locations' => [
                'required',
            ],

            'factions' => [
                'required',
            ],

            'creatures' => [
                'required',
            ],

            'systems' => [
                'required',
            ],

            'timeline' => [
                'required',
            ],

            'story_structure' => [
                'required',
            ],

            'twists_and_foreshadowing' => [
                'required',
            ],

            'scene_plans' => [
                'required',
            ],

            'dialogue_plans' => [
                'required',
            ],

            'chapter_plan' => [
                'required',
            ],

            'page_plan' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'foundation.required'                => 'Foundation is required.',
            'characters.required'                => 'Characters are required.',
            'world_bible.required'               => 'World Bible is required.',
            'locations.required'                 => 'Locations are required.',
            'factions.required'                  => 'Factions are required.',
            'creatures.required'                 => 'Creatures are required.',
            'systems.required'                   => 'Systems are required.',
            'timeline.required'                  => 'Timeline is required.',
            'story_structure.required'           => 'Story Structure is required.',
            'twists_and_foreshadowing.required'  => 'Twists and Foreshadowing is required.',
            'scene_plans.required'               => 'Scene Plans are required.',
            'dialogue_plans.required'            => 'Dialogue Plans are required.',
            'chapter_plan.required'              => 'Chapter Plan is required.',
            'page_plan.required'                 => 'Page Plan is required.',
            'additional_information.string'      => 'Additional Information must be a string.',
        ];
    }
}