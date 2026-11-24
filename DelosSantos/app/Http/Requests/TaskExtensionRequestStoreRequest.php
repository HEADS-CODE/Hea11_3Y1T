<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskExtensionRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMember() === true
            && (int) $this->route('task')?->member_id === (int) $this->user()?->member_id;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
