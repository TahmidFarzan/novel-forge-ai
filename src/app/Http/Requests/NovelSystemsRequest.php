<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelSystemsRequest extends FormRequest
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

            'creatures' => [
                'required',
            ],

            'factions' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'world_bible.required'           => 'World Bible is required.',
            'creatures.required'             => 'Creatures are required.',
            'factions.required'              => 'Factions are required.',
        ];
    }
}