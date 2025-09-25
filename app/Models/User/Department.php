<?php

namespace App\Models\User;

use App\Models\Edo\EdoEvent;
use App\Models\Education\EducationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function edoEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EdoEvent::class);
    }

    public function educationEvents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EducationEvent::class);
    }
}
