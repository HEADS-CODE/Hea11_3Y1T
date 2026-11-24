<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isMember() && auth()->user()->member !== null;
    }

    public function rules(): array
    {
        $member = $this->user()?->member;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member?->id), Rule::unique('users', 'email')->where(fn ($query) => $query->where('role', User::ROLE_MEMBER))->ignore($member?->user?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(config('membership.genders'))],
            'unit_floor_building' => ['nullable', 'string', 'max:150'],
            'house_street' => ['nullable', 'string', 'max:150'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'city_municipality' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'digits:4'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'privacy_consent' => ['nullable'],
            'documents' => ['nullable', 'array'],
            'documents.*' => $this->documentRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $member = $this->user()?->member;
            if (! $member) {
                return;
            }

            $identityChanged = collect(['first_name', 'middle_name', 'last_name', 'birth_date'])
                ->contains(fn (string $field): bool => $this->normalise($member->getAttribute($field), $field) !== $this->normalise($this->input($field), $field));
            $hasDocuments = $this->hasFile('documents') && count((array) $this->file('documents')) > 0;

            if ($identityChanged && ! $hasDocuments) {
                $validator->errors()->add('documents', 'A name or birth-date change requires a supporting document.');
            }

            if ($hasDocuments && ! $this->boolean('privacy_consent')) {
                $validator->errors()->add('privacy_consent', 'Please provide privacy consent before uploading documents.');
            }
        });
    }

    private function normalise(mixed $value, string $field): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) ($value ?? '');
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
