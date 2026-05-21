<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_LEADER = 'leader';
    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, [self::ROLE_LEADER, self::ROLE_MEMBER], true);
    }

    public function owned_projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)
            ->withPivot('role_in_project')
            ->withTimestamps();
    }

    public function assigned_tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function created_tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'creator_id');
    }

    public function is_leader(): bool
    {
        return $this->role === self::ROLE_LEADER;
    }
}
