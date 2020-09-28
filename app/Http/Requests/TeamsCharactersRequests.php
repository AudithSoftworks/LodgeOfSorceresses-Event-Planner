<?php

/**
 * @noinspection PhpUnusedParameterInspection
 * @todo Remove this when upgrading to PHP-8, where we can have argument types without arguments
 */

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamsCharactersRequests extends FormRequest
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
        $requestMethod = $request->getMethod();
        if ($requestMethod === 'POST') {
            return [
                'characterIds' => 'required|array',
                'characterIds.*' => 'required|numeric|exists:characters,id',
            ];
        }
        if ($requestMethod === 'PUT') {
            return [
                'accepted_terms' => 'required|accepted',
            ];
        }
        return [];
    }

    public function messages(): array
    {
        return [
            'accepted_terms.accepted' => 'Please make sure you accept the terms of membership.',
            'characterIds.required' => 'Select the character(s) to be added to the team.',
            'characterIds.*.exists' => 'No such characters exist.',
        ];
    }
}
