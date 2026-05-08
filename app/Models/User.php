<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
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
        'role_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouse');
    }

    public function hasPermission($permission)
    {
        if (!$this->role) return false;
        if (in_array('all', $this->role->permissions ?? [])) return true;

        // If user has the specific permission, return true
        if (in_array($permission, $this->role->permissions ?? [])) return true;

        // Fallback: If user has '[module]_view', allow all actions for that module
        // Example: if they have 'master_item_view', they also get 'master_item_create' etc.
        if (str_contains($permission, '_')) {
            $parts = explode('_', $permission);
            array_pop($parts);
            $moduleBase = implode('_', $parts);
            return in_array($moduleBase . '_view', $this->role->permissions ?? []);
        }

        return false;
    }

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
}
