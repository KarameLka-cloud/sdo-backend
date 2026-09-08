<?php

namespace App\Models\Mentorship;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdaptationPlanTask extends Model
{
    protected $fillable = [
        'adaptation_plan_day_id',
        'description',
        'status',
        'responsible_role',
        'links',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'links' => 'array',
        'status' => TaskStatus::class,
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(AdaptationPlanDay::class, 'adaptation_plan_day_id');
    }
}
