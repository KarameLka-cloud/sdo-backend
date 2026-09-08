<?php

namespace App\Models\Mentorship;

use App\Enums\CompletionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdaptationPlanDay extends Model
{
    protected $fillable = [
        'adaptation_plan_id',
        'work_day',
        'day_from',
        'day_to',
        'date_from',
        'date_to',
        'completion',
        'employee_comment',
        'intern_comment',
        'mentor_comment',
        'department_head_comment',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'date_from' => 'date:Y-m-d',
        'date_to' => 'date:Y-m-d',
        'day_from' => 'integer',
        'day_to' => 'integer',
        'completion' => CompletionStatus::class,
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AdaptationPlan::class, 'adaptation_plan_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(AdaptationPlanTask::class, 'adaptation_plan_day_id');
    }
}
