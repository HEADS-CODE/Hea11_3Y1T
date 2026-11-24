<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isMember() && auth()->user()->member !== null;
    }

    public function rules(): array
    {
        return [
            'membership_category_id' => ['required', Rule::exists('membership_categories', 'id')->where(fn ($query) => $query->where('is_active', true))],
            'privacy_consent' => ['accepted'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => $this->documentRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $member = $this->user()?->member;

            if ($member && (int) $this->input('membership_category_id') === (int) $member->membership_category_id) {
                $validator->errors()->add('membership_category_id', 'Please choose a different category for a category-change request.');
            }
        });
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
