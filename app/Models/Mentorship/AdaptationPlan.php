<?php

namespace App\Models\Mentorship;

use App\Enums\UserRole;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdaptationPlan extends Model
{
    protected $fillable = [
        'user_id',
        'adaptation_plan_template_id',
        'start_date',
        'work_schedule',
        'shift',
        'mentor',
        'supervisor',
        'department_head',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AdaptationPlanTemplate::class, 'adaptation_plan_template_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(AdaptationPlanDay::class, 'adaptation_plan_id')->orderBy('work_day');
    }

    public function mentorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor');
    }

    public function supervisorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor');
    }

    public function departmentHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head');
    }

    /**
     * Limits a listing to the plans the given user is allowed to see:
     * admins see everything, mentors/supervisors/heads see plans where they
     * are assigned, and everyone else sees only their own plan.
     */
    public function scopeVisibleTo(Builder $query, User $user, ?UserRole $role): Builder
    {
        if ($role === UserRole::ADMIN) {
            return $query;
        }

        if ($role === UserRole::MENTOR) {
            return $query->where('mentor', $user->id);
        }

        if ($role === UserRole::SUPERVISOR) {
            return $query->where('supervisor', $user->id);
        }

        if ($role === UserRole::DEPARTMENT_HEAD) {
            return $query->where('department_head', $user->id);
        }

        return $query->where('user_id', $user->id);
    }

    public function isVisibleTo(User $user, ?UserRole $role): bool
    {
        if ($this->isManageableBy($user, $role)) {
            return true;
        }

        if ($role === UserRole::DEPARTMENT_HEAD) {
            return (int) $this->department_head === (int) $user->id;
        }

        return (int) $this->user_id === (int) $user->id;
    }

    public function isManageableBy(User $user, ?UserRole $role): bool
    {
        if ($role === UserRole::ADMIN) {
            return true;
        }

        if ($role === UserRole::MENTOR) {
            return (int) $this->mentor === (int) $user->id;
        }

        if ($role === UserRole::SUPERVISOR) {
            return $this->supervisor !== null
                && (int) $this->supervisor === (int) $user->id;
        }

        return false;
    }
}
