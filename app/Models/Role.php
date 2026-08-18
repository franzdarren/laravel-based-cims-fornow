<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'role';

    protected $primaryKey = 'role_id';

    protected $fillable = ['role_name', 'role_description', 'status'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            RolePermission::class,
            'role_permission_bridge',
            'role_id',
            'role_permission_id',
            'role_id',
            'role_permission_id'
        );
    }

    public function permissionKeys(): array
    {
        return $this->permissions()->pluck('role_permission_name')->all();
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, $this->permissionKeys());
    }

    public function activeUsersCount(): int
    {
        return $this->users()->where('is_active', true)->count();
    }
}
