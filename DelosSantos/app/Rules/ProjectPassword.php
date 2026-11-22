<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

final class ProjectPassword
{
    private function __construct()
    {
    }

    public static function rules(): array
    {
        return ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()];
    }
}
