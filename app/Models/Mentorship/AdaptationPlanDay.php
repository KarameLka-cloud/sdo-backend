<?php

namespace App\Models\Mentorship;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdaptationPlanDay extends Model
{
    protected $fillable = [
        'adaptation_plan_id',
        'work_day',
        'date',
        'completion',
        'employee_comment',
        'intern_comment',
        'mentor_comment',
        'department_head_comment',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
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
