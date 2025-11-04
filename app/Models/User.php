<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'password',
        'role',
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
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is normal user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get user permissions
     */
    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * Check if user has permission for a module
     */
    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin has all permissions
        }

        $permission = $this->permissions()->where('module', $module)->first();
        
        if (!$permission) {
            return false;
        }

        return $action === 'edit' ? $permission->can_edit : $permission->can_delete;
    }

    /**
     * Check if user can edit in a module
     */
    public function canEdit(string $module): bool
    {
        return $this->hasPermission($module, 'edit');
    }

    /**
     * Check if user can delete in a module
     */
    public function canDelete(string $module): bool
    {
        return $this->hasPermission($module, 'delete');
    }
}
