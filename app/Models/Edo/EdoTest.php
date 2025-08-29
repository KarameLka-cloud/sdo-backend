<?php

namespace App\Models\Edo;

use Illuminate\Database\Eloquent\Model;

class EdoTest extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
