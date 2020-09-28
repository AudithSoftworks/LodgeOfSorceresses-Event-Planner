<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DpsParsesRequests extends FormRequest
{
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function authorize(Request $request): bool
    {
        return Gate::allows('has-app-access');
    }

    public function rules(Request $request): array
    {
        if ($request->getMethod() === 'POST') {
            return [
                'parse_file_hash' => 'required|string|exists:files,hash',
                'info_file_hash' => 'required|string|exists:files,hash',
                'dps_amount' => 'required|numeric',
                'sets' => 'required|array|between:2,5',
                'sets.*' => 'required|numeric|exists:sets,id',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'parse_file_hash.required' => 'CMX Combat screen screenshot needs to be uploaded.',
            'parse_file_hash.exists' => 'CMX Combat screen screenshot file not found.',
            'info_file_hash.required' => 'CMX Info screen screenshot needs to be uploaded.',
            'info_file_hash.exists' => 'CMX Info screen screenshot file not found.',
            'dps_amount.required' => 'DPS Number is required.',
            'sets.required' => 'Provide the list of Sets worn during Parse.',
            'sets.between' => 'Number of sets worn during Parse should be between 2 and 5.',
            'sets.*.required' => 'Select sets worn during the parse.',
            'sets.*.exists' => 'One or more invalid Sets provided.',
        ];
    }
}
