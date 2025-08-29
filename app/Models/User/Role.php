<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
