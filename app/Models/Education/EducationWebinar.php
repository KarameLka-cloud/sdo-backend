<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationWebinar extends Model
{
    protected $fillable = [
        'title',
        'link',
        'time',
        'date',
        'duration',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
