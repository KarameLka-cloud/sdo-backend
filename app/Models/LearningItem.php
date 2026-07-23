<?php

namespace App\Models;

use App\Models\User\Department;
use App\Models\User\Position;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningItem extends Model
{
    public const CATEGORY_EDUCATION = 'education';
    public const CATEGORY_EDO = 'edo';

    public const TYPE_EVENT = 'event';
    public const TYPE_COURSE = 'course';
    public const TYPE_WEBINAR = 'webinar';
    public const TYPE_TEST = 'test';

    public const CATEGORIES = [
        self::CATEGORY_EDUCATION,
        self::CATEGORY_EDO,
    ];

    public const TYPES = [
        self::TYPE_EVENT,
        self::TYPE_COURSE,
        self::TYPE_WEBINAR,
        self::TYPE_TEST,
    ];

    protected $fillable = [
        'category',
        'type',
        'title',
        'description',
        'link',
        'department_id',
        'note_department',
        'position_id',
        'note_position',
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
        'position',
    ];

    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function positionRelation(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function getDepartmentAttribute(): ?string
    {
        return $this->departmentRelation()->pluck('name')->first();
    }

    public function getPositionAttribute(): ?string
    {
        return $this->positionRelation()->pluck('name')->first();
    }
}
