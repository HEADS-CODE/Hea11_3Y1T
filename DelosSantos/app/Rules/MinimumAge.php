<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

final class MinimumAge implements ValidationRule
{
    public function __construct(
        private readonly int $minimumAge,
        private readonly string $message,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        try {
            $birthDate = Carbon::parse((string) $value)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        if ($birthDate->isAfter(today()->subYears($this->minimumAge))) {
            $fail($this->message);
        }
    }
}
