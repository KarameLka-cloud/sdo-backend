<?php

namespace App\Services\User;

use App\Enums\UserRole;

class RoleResolver
{
    public function resolve(?string $role): ?UserRole
    {
        return $role === null
            ? null
            : UserRole::tryFrom(trim($role));
    }
}
