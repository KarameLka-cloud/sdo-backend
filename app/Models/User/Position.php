<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Edo\EdoTest;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function edoTests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EdoTest::class);
    }
}
