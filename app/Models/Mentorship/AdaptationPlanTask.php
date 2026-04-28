<?php

namespace App\Models\Mentorship;

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

    protected $casts = [
        'links' => 'array',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(AdaptationPlanDay::class, 'adaptation_plan_day_id');
    }
}
