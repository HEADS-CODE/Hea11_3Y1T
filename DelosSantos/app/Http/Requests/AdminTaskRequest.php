<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Member;
use App\Models\Task;
use Illuminate\Validation\Rule;

class AdminTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value === 'all' && ! $this->routeIs('admin.tasks.update')) {
                    return;
                }

                $isActiveMember = Member::query()
                    ->whereKey($value)
                    ->where('status', 'active')
                    ->whereNull('archived_at')
                    ->exists();

                if (! $isActiveMember) {
                    $fail('Choose an active, non-archived member.');
                }
            }],
            'task_title' => ['required', 'string', 'max:255'],
            'priority' => ['sometimes', Rule::in(Task::PRIORITIES)],
            'description' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['nullable', 'date'],
        ];
    }
}
