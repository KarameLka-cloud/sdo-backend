<?php

namespace App\Models\Mentorship;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdaptationPlan extends Model
{
    protected $fillable = [
        'user_id',
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

    public function mentorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor');
    }

    public function departmentHeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'department_head');
    }
}
