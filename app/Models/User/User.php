<?php

namespace App\Models\User;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    use AuthenticatesWithLdap, HasApiTokens, Notifiable;

    /**
     * Attributes populated from LDAP on sign-in. `password` is deliberately
     * excluded: credentials live in the directory, not in this table.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'department',
        'company',
        'login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * `login` is hidden by default so it does not leak through nested payloads
     * (an intern loading their plan also loads mentor and head records).
     * The staff roster re-exposes it with `makeVisible`.
     *
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'guid',
        'domain',
        'login',
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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function getRoleAttribute()
    {
        $this->loadMissing('roles');

        return $this->roles->first()?->name;
    }

    public function getRoleNameAttribute()
    {
        $this->loadMissing('roles');

        $role = $this->roles->first();

        return UserRole::displayName($role?->name) ?? $role?->display_name;
    }
}
