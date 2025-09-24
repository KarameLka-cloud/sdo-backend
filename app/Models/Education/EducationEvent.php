<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'department',
        'time',
        'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
