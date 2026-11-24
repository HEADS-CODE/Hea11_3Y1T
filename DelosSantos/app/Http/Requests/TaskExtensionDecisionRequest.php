<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskExtensionDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'new_deadline' => [
                Rule::requiredIf(fn (): bool => $this->routeIs('admin.task-extension-requests.approve')),
                'nullable',
                'date',
                'after:today',
            ],
            'decision_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
