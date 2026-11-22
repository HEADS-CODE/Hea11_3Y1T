<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhilippinePhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== null && $value !== '' && ! preg_match('/^(09\d{9}|\+639\d{9})$/', (string) $value)) {
            $fail('Enter a Philippine mobile number in the format 09XXXXXXXXX or +639XXXXXXXXX.');
        }
    }
}
