<?php

namespace App\Models\User;

use App\Models\Mentorship\AdaptationPlan;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use Notifiable, HasApiTokens, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'department',
        'company',
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'guid',
        'domain',
        'password',
        'remember_token',
        'roles',
    ];

    protected $appends = ['role', 'role_name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function adaptationPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AdaptationPlan::class);
    }

    public function getRoleAttribute()
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->first()?->name;
        }

        return $this->roles()->pluck('name')->first();
    }

    public function getRoleNameAttribute()
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->first()?->display_name;
        }

        return $this->roles()->pluck('display_name')->first();
    }
}
