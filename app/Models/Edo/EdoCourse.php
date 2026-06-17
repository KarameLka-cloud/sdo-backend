<?php

namespace App\Models\Edo;

use App\Models\User\Department;
use Illuminate\Database\Eloquent\Model;

class EdoCourse extends Model
{
    protected $fillable = [
        'title',
        'link',
        'department_id',
        'note_department',
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
