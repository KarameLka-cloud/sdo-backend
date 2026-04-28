<?php

namespace App\Models\Mentorship;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdaptationPlanTemplate extends Model
{
    protected $fillable = [
        'name',
        'work_schedule',
        'shifts',
        'task_blueprint',
    ];

    protected $casts = [
        'shifts' => 'array',
        'task_blueprint' => 'array',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(AdaptationPlan::class, 'adaptation_plan_template_id');
    }
}
