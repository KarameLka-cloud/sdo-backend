<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationWebinar extends Model
{
    protected $fillable = [
        'title',
        'time_start',
        'time_end',
        'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
