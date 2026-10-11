<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_super_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /* -------------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------------- */

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')->withTimestamps();
    }

    /* -------------------------------------------------------------------------
     | Helpers & Roles
     | ------------------------------------------------------------------------- */

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer' && !$this->isSuperAdmin();
    }

    /**
     * Check if user has specific role or any of the given roles.
     *
     * @param string|array<string> $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $roleSlugs = is_array($roles) ? $roles : func_get_args();

        return $this->roles->pluck('slug')->intersect($roleSlugs)->isNotEmpty();
    }

    /**
     * Check if user has a given permission (via roles or direct user assignment).
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check direct permissions
        if ($this->directPermissions->contains('slug', $permissionSlug)) {
            return true;
        }

        // Check permissions through assigned roles
        return $this->roles->flatMap(fn($role) => $role->permissions)->contains('slug', $permissionSlug);
    }

    /**
     * Get all effective permissions (roles + direct).
     */
    public function getAllPermissions(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        $rolePermissions = $this->roles->flatMap(fn($role) => $role->permissions);
        return $rolePermissions->merge($this->directPermissions)->unique('id');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Sync user roles.
     *
     * @param array<int|string|Role> $roles
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = [];
        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $roleIds[] = $role->id;
            } elseif (is_numeric($role)) {
                $roleIds[] = (int) $role;
            } elseif (is_string($role)) {
                $found = Role::where('slug', $role)->first();
                if ($found) {
                    $roleIds[] = $found->id;
                }
            }
        }

        $this->roles()->sync($roleIds);
    }

    /* -------------------------------------------------------------------------
     | Scopes
     | ------------------------------------------------------------------------- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('role', 'admin')->orWhere('is_super_admin', true);
        });
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', 'customer')->where('is_super_admin', false);
    }
}
