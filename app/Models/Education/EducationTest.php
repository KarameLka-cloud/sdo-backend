<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;

class EducationTest extends Model
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
