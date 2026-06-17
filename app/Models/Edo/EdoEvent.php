<?php

namespace App\Models\Edo;

use App\Models\User\Department;
use Illuminate\Database\Eloquent\Model;

class EdoEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'link',
        'department_id',
        'note_department',
        'time',
        'date',
        'duration',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'department',
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function getDepartmentAttribute()
    {
        return $this->department()->pluck('name')->first();
    }
}
