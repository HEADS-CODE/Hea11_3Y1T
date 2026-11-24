<?php

namespace App\Http\Requests;

use App\Rules\ProjectPassword;
use Illuminate\Foundation\Http\FormRequest;

class AcceptMemberInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ProjectPassword::rules(),
        ];
    }
}
