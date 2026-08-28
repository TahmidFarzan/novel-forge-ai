<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthUserAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => [
                "required",
                "string",
                "max:200",
            ],

            "email" => [
                "required",
                "email",
                "max:255",
                Rule::unique('users')->ignore(Auth::id(), 'id'),
            ],

            "change_password" => [
                "required",
                "boolean",
            ],

            "password" => [
                "nullable",
                "string",
                "min:8",
                "confirmed",
            ],

            "current_password" => [
                "nullable",
                "string",
            ],

            "password_confirmation" => [
                "nullable",
                "string",
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $data = $validator->getData();

            if (
                isset($data["change_password"]) &&
                $data["change_password"] == true
            ) {

                if (empty($data["password"])) {
                    $validator->errors()->add(
                        "password",
                        "The password field is required when changing password."
                    );
                }

                if (empty($data["current_password"])) {
                    $validator->errors()->add(
                        "current_password",
                        "The current password field is required."
                    );
                }

                if (empty($data["password_confirmation"])) {
                    $validator->errors()->add(
                        "password_confirmation",
                        "The password confirmation field is required."
                    );
                }
            }

        });
    }
}
