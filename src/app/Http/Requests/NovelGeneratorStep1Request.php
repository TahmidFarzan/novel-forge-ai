<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NovelGeneratorStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'main_character_gender'  => [
                'required',
                'in:Male,Female',
            ],

            'is_18_plus'             => [
                'nullable',
                'boolean',
            ],

            'enable_mature_content'  => [
                'nullable',
                'boolean',
            ],

            'additional_information' => [
                'nullable',
                'string',
            ],

            'novel_continuity'       => [
                'required',
                'string',
            ],
            'language'               => [
                'required',
                'string',
            ],

            'genre_ids'              => [
                'nullable',
                'array',
            ],

            'genre_ids.*'            => [
                'integer',
                'exists:genres,id',
            ],

            'ai_brain_id'            => [
                'required',
                'integer',
                'exists:ai_brains,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'main_character_gender.required' => 'Please select a main character gender.',

            'novel_continuity.required'      => 'Please select a novel continuity.',
            'language.required'              => 'Please select a language.',

            'genre_ids.array'                => 'Genres must be selected as an array.',

            'genre_ids.*.exists'             => 'Selected genre does not exist.',

            'ai_brain_id.required'           => 'Please select a ai brain.',
            'ai_brain_id.exists'             => 'Selected ai brain does not exist.',
        ];
    }

    public function withValidator($validator)
    {

        $validator->after(function ($validator) {
            $aVData = $validator->getData();

            if (isset($aVData['enable_mature_content']) && ($aVData['enable_mature_content'] == true)) {

                if (! isset($aVData['is_18_plus']) || (isset($aVData['is_18_plus']) && ($aVData['is_18_plus'] == false))) {

                    $validator->errors()->add(
                        'enable_mature_content',
                        'Enable mature content can not true as is 18+ is false'
                    );
                }
            }
        });
    }
}
