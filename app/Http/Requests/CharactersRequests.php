<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CharactersRequests extends FormRequest
{
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function authorize(): bool
    {
        return Gate::allows('has-app-access');
    }

    public function rules(Request $request): array
    {
        $requestMethod = $request->getMethod();
        if ($requestMethod === 'POST') {
            return [
                'name' => 'required|string',
                'role' => 'required|integer|min:1|max:4',
                'class' => 'required|integer|min:1|max:6',
                'content' => 'nullable|array',
                'content.*' => 'nullable|numeric|exists:content,id',
                'sets' => 'required|array',
                'sets.*' => 'required|numeric|exists:sets,id',
                'skills' => 'nullable|array',
                'skills.*' => 'nullable|numeric|exists:skills,id',
            ];
        }
        if ($requestMethod === 'PUT') {
            return [
                'name' => 'sometimes|required|string',
                'role' => 'sometimes|required|integer|min:1|max:4',
                'class' => 'sometimes|required|integer|min:1|max:6',
                'content' => 'nullable|array',
                'content.*' => 'nullable|numeric|exists:content,id',
                'sets' => 'sometimes|required|array',
                'sets.*' => 'required|numeric|exists:sets,id',
                'skills' => 'nullable|array',
                'skills.*' => 'nullable|numeric|exists:skills,id',
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Character name is required.',
            'name.string' => 'Character name must be string.',
            'role.required' => 'Choose a role.',
            'role.integer' => 'Role must be an integer.',
            'role.min' => 'Role must be an integer between 1 and 4.',
            'role.max' => 'Role must be an integer between 1 and 4.',
            'class.required' => 'Choose a class.',
            'class.integer' => 'Class must be an integer.',
            'class.min' => 'Class must be an integer between 1 and 6.',
            'class.max' => 'Class must be an integer between 1 and 6.',
            'content.array' => 'Content must be an array of integers.',
            'content.*.numeric' => 'Content must be an integer.',
            'content.*.exists' => 'One or more of given content doesn\'t exist.',
            'sets.required' => 'Select all full sets your Character has.',
            'sets.array' => 'Sets must be an array of integers.',
            'sets.*.required' => 'Select all full sets your Character has.',
            'sets.*.numeric' => 'Set must be an integer.',
            'sets.*.exists' => 'One or more of given sets doesn\'t exist.',
            'skills.array' => 'Skills must be an array of integers.',
            'skills.*.required' => 'Select all support skills your Character has unlocked and fully leveled.',
            'skills.*.exists' => 'One or more of given skills doesn\'t exist.',
            'skills.*.numeric' => 'Skill must be an integer.',
        ];
    }
}
