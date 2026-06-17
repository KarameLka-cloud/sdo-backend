<?php

namespace App\Models\Education;

use App\Models\User\Position;
use Illuminate\Database\Eloquent\Model;

class EducationTest extends Model
{
    protected $fillable = [
        'title',
        'link',
        'position_id',
        'note_position',
        'date',
        'duration',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'position',
    ];

    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function getPositionAttribute()
    {
        return $this->position()->pluck('name')->first();
    }
}
