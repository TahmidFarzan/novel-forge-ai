<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'birth_date' => [
                'nullable',
                'date',
            ],

            'gender' => [
                'required',
                'in:Male,Female,Other',
            ],

            'religion' => [
                'required',
                'in:Islam,Hindu,Christian,Other',
            ],

            'marital_status' => [
                'required',
                'in:Single,Married,Divorced,Separated,Other',
            ],

            'mobile' => [
                'nullable',
                'max:20',
                'regex:/^[+0-9 ]+$/',
            ],

            'address' => [
                'nullable',
            ],
        ];
    }
}
