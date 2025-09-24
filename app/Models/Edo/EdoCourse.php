<?php

namespace App\Models\Edo;

use Illuminate\Database\Eloquent\Model;

class EdoCourse extends Model
{
    protected $fillable = [
        'title',
        'url',
        'date_end',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
