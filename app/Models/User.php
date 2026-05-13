<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'email',
        'password',
        'is_super_admin',
        'platform_role',
        'photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
    ];

    public function isPlatformStaff(array $roles = []): bool
    {
        if ($this->is_super_admin || $this->platform_role === 'super_admin') {
            return true;
        }

        return $roles === [] ? $this->platform_role !== 'customer' : in_array($this->platform_role, $roles, true);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function isOwnerOrSuperAdmin(): bool
    {
        if ($this->is_super_admin || $this->platform_role === 'super_admin') {
            return true;
        }

        return $this->roles->contains('slug', 'owner');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isOwnerOrSuperAdmin()) {
            return true;
        }

        return $this->roles->flatMap->permissions->contains($permission);
    }
}
