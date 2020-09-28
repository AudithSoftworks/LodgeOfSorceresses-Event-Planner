<?php

namespace App\Http\Requests;

use DateTimeInterface;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AttendancesRequests extends FormRequest
{
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function authorize(): bool
    {
        return Gate::allows('has-app-access');
    }

    public function rules(): array
    {
        return [
            'b' => 'sometimes|date_format:' . DateTimeInterface::RFC3339_EXTENDED,
        ];
    }

    public function messages(): array
    {
        return [
            'b.date_format' => '"b" parameter needs to be in ISODate format.',
        ];
    }

}
