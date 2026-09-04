<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Mentorship\AdaptationPlan;
use App\Models\User\User;
use App\Services\User\RoleResolver;

class AdaptationPlanPolicy
{
    public function __construct(
        private readonly RoleResolver $roleResolver,
    ) {}

    public function view(User $user, AdaptationPlan $plan): bool
    {
        return $plan->isVisibleTo($user, $this->resolveRole($user));
    }

    public function create(User $user): bool
    {
        return in_array($this->resolveRole($user), [
            UserRole::ADMIN,
            UserRole::MENTOR,
            UserRole::DEPARTMENT_HEAD,
        ], true);
    }

    public function manage(User $user, AdaptationPlan $plan): bool
    {
        return $plan->isManageableBy($user, $this->resolveRole($user));
    }

    public function update(User $user, AdaptationPlan $plan): bool
    {
        return $this->manage($user, $plan);
    }

    public function delete(User $user, AdaptationPlan $plan): bool
    {
        return $this->manage($user, $plan);
    }

    private function resolveRole(User $user): ?UserRole
    {
        $user->loadMissing('roles');

        return $this->roleResolver->resolve($user->role);
    }
}
