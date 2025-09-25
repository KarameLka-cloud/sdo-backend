<?php

namespace App\Models\Education;

use App\Models\User\Department;
use Illuminate\Database\Eloquent\Model;

class EducationEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department_id',
        'time',
        'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'department'
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
