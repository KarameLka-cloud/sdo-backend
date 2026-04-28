<?php

namespace App\Models\Mentorship;

use App\Models\User\User;
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

    public function departmentHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head');
    }
}
