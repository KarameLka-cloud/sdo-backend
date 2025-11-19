<?php

namespace App\Models\Edo;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\Position;

class EdoTest extends Model
{
    protected $fillable = [
        'title',
        'url',
        'position_id',
        'note_position',
        'date_end',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'position',
    ];

    public function positions(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function getPositionAttribute()
    {
        return $this->positions()->pluck('name')->first();
    }
}
