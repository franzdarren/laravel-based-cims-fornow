<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'user_id';

    // The table only has created_at (no updated_at), per the team's ERD.
    public $timestamps = false;

    protected $fillable = [
        'fullname', 'email', 'password', 'role_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function isRole(string $name): bool
    {
        return $this->role && strcasecmp($this->role->role_name, $name) === 0;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
