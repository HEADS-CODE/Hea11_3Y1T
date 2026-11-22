<?php

namespace App\Policies;

use App\Models\MemberDocument;
use App\Models\User;

class MemberDocumentPolicy
{
    public function view(User $user, MemberDocument $memberDocument): bool
    {
        return $user->isAdmin() && $user->isActive() && ! $user->isBlocked();
    }
}
