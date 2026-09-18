<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelChapterContentRequest extends FormRequest
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
            'chapter_no' => [
                'required',
                'string',
            ],

            'ai_brain_id' => [
                'required',
                'integer',
            ],

            'additional_information' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages()
    {
        return [
            'chapter_no.required'             => 'Chapter Number is required.',
            'ai_brain_id.required'            => 'AI Brain is required.',
            'additional_information.string'   => 'Additional Information must be a string.',
        ];
    }
}