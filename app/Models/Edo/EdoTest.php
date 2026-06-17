<?php

namespace App\Models\Edo;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\Position;

class EdoTest extends Model
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
