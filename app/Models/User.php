<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Safely set the user's role.
     *
     * @param string $role The role to assign (e.g., 'admin', 'user')
     * @param User|null $actor The user performing the action (for authorization)
     * @return bool True if the role was set successfully, false otherwise
     */
    public function setRole(string $role, ?User $actor = null): bool
    {
        // Define allowed roles
        $allowedRoles = ['user', 'admin'];

        // Validate the provided role
        if (!in_array($role, $allowedRoles)) {
            return false;
        }

        // Optional: Add authorization check (e.g., only admins can change roles)
        if ($actor && !$actor->isAdmin()) {
            return false; // Only admins can change roles
        }

        // Update the role
        $this->role = $role;
        return $this->save();
    }
}
