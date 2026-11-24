<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReactivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->isMember()
            && auth()->user()->member?->status === 'inactive'
            && auth()->user()->member?->archived_at === null;
    }

    public function rules(): array
    {
        return [
            'privacy_consent' => ['accepted'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => $this->documentRules(),
        ];
    }

    private function documentRules(): array
    {
        return [
            'file',
            'max:'.config('membership.documents.max_kilobytes'),
            'mimetypes:'.implode(',', config('membership.documents.mimetypes')),
            'mimes:'.implode(',', config('membership.documents.extensions')),
        ];
    }
}
