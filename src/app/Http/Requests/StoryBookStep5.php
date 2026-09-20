<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoryBookStep5 extends FormRequest
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
            'world_bible' => [
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
            'world_bible.required'           => 'World Bible is required.',
            'locations.required'             => 'Locations are required.',
        ];
    }
}