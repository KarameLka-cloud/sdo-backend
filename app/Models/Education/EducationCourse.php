<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationCourse extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
