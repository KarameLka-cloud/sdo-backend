<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];
}
