<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NovelPagePlannerRequest extends FormRequest
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
            'chapter_plan' => [
                'required',
            ],

            'scene_plans' => [
                'required',
            ],
        ];
    }

    public function messages()
    {
        return [
            'chapter_plan.required'             => 'Chapter Plan is required.',
            'scene_plans.required'              => 'Scene Plans are required.',
        ];
    }
}