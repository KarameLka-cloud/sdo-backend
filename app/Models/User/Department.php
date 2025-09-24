<?php

namespace App\Models\User;

use App\Models\Edo\EdoEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function edoEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EdoEvent::class);
    }
}
