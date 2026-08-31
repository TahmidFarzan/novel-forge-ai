<?php
namespace App\Http\Requests;

use App\Helpers\SettingHelper;
use App\Models\AiBrainRunner;
use App\Models\AiBrain;
use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
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
        $setting = Setting::where('slug', $this->route('slug'))->first();

        return [
            'name'                => [
                'required',
                'string',
                'max:255',
                Rule::unique('settings', 'name')->ignore($setting?->id),
            ],

            'options'             => [
                'required',
                'array',
            ],

            'options.*'           => [
                'required',
                'array',
            ],

            'options.*.valueType' => [
                'required',
                'string',
                Rule::in($this->allowedValueTypes()),
            ],

            'options.*.value'     => [
                'nullable',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'The setting name is required.',
            'name.string'                  => 'The setting name must be a string.',
            'name.max'                     => 'The setting name may not be greater than 255 characters.',
            'name.unique'                  => 'This setting name has already been taken.',

            'options.required'             => 'The options field is required.',
            'options.array'                => 'The options must be an array.',

            'options.*.required'           => 'Each option must be an array.',
            'options.*.array'              => 'Each option must be an array.',

            'options.*.valueType.required' => 'The value type is required for each option.',
            'options.*.valueType.string'   => 'The value type must be a string.',
            'options.*.valueType.in'       => 'The value type must be a valid option type.',

            'options.*.value.required'     => 'The value is required.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $options = $this->input('options', []);

            if (! is_array($options)) {
                return;
            }

            foreach ($options as $key => $option) {
                if (! is_array($option)) {
                    continue;
                }

                $valueType = $option['valueType'] ?? null;
                $value     = $option['value'] ?? null;

                if (! $valueType) {
                    continue;
                }

                if (! in_array($valueType, $this->allowedValueTypes(), true)) {
                    continue;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                if (! $this->isValidSettingValue($valueType, $value)) {
                    $validator->errors()->add(
                        "options.{$key}.value",
                        'Invalid value type.'
                    );
                }

                if ($key === SettingHelper::OPTION_AI_BRAIN_RUNNER) {
                    if (AiBrainRunner::where("id", $value)->exists() == 0) {
                        $validator->errors()->add(
                            "options.{$key}.value",
                            'The selected AI Brain Runner is invalid.'
                        );
                    }
                }

                if ($key === SettingHelper::OPTION_AI_BRAIN) {
                    if (AiBrain::where("id", $value)->exists() == 0) {
                        $validator->errors()->add(
                            "options.{$key}.value",
                            'The selected AI Brain is invalid.'
                        );
                    }
                }
            }
        });
    }

    private function allowedValueTypes(): array
    {
        return [
            SettingHelper::OPTION_VALUE_TYPE_TEXT,
            SettingHelper::OPTION_VALUE_TYPE_STRING,
            SettingHelper::OPTION_VALUE_TYPE_BOOLEAN,
            SettingHelper::OPTION_VALUE_TYPE_INTEGER,
            SettingHelper::OPTION_VALUE_TYPE_FLOAT,
            SettingHelper::OPTION_VALUE_TYPE_DECIMAL,
            SettingHelper::OPTION_VALUE_TYPE_JSON,
            SettingHelper::OPTION_VALUE_TYPE_ARRAY,
            SettingHelper::OPTION_VALUE_TYPE_URL,
            SettingHelper::OPTION_VALUE_TYPE_IMAGE,
            SettingHelper::OPTION_VALUE_TYPE_COLOR,
        ];
    }

    private function isValidSettingValue(string $type, mixed $value): bool
    {
        return match ($type) {
            SettingHelper::OPTION_VALUE_TYPE_TEXT,
            SettingHelper::OPTION_VALUE_TYPE_STRING  => is_string($value),

            SettingHelper::OPTION_VALUE_TYPE_BOOLEAN => is_bool($value)
            || filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) !== null,

            SettingHelper::OPTION_VALUE_TYPE_INTEGER => is_int($value)
            || filter_var($value, FILTER_VALIDATE_INT) !== false,

            SettingHelper::OPTION_VALUE_TYPE_FLOAT   => is_float($value)
            || filter_var($value, FILTER_VALIDATE_FLOAT) !== false,

            SettingHelper::OPTION_VALUE_TYPE_DECIMAL => is_numeric($value)
            && preg_match(
                '/^-?\d+(\.\d+)?$/',
                (string) $value
            ),

            SettingHelper::OPTION_VALUE_TYPE_JSON    => $this->isValidJson($value),

            SettingHelper::OPTION_VALUE_TYPE_ARRAY   => is_array($value),

            SettingHelper::OPTION_VALUE_TYPE_URL     => is_string($value)
            && filter_var($value, FILTER_VALIDATE_URL) !== false,

            SettingHelper::OPTION_VALUE_TYPE_IMAGE   => $value instanceof UploadedFile
            && str_starts_with($value->getMimeType(), 'image/'),

            SettingHelper::OPTION_VALUE_TYPE_COLOR   => is_string($value)
            && preg_match(
                '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/',
                $value
            ),

            default                                  => false,
        };
    }

    private function isValidJson(mixed $value): bool
    {
        if (is_array($value) || is_object($value)) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
