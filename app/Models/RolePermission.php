<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RolePermission extends Model
{
    protected $table = 'role_permission';

    protected $primaryKey = 'role_permission_id';

    protected $fillable = ['role_permission_name', 'role_permission_description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission_bridge',
            'role_permission_id',
            'role_id',
            'role_permission_id',
            'role_id'
        );
    }
}
