<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationEvent extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
