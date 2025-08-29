<?php

namespace App\Models\Edo;

use Illuminate\Database\Eloquent\Model;

class EdoEvent extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
