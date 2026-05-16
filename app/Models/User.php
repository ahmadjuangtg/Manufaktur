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
        $userPerms = $this->role->permissions ?? [];
        if (in_array('all', $userPerms)) return true;
        if (in_array($permission, $userPerms)) return true;

        // Module-wide permission fallback: 
        // If checking for 'order_view', allow if they have ANY 'order_***_view'
        if (str_ends_with($permission, '_view')) {
            $module = str_replace('_view', '', $permission);
            foreach ($userPerms as $p) {
                if (str_starts_with($p, $module . '_')) return true;
            }
        }

        // Action fallback: 
        // If checking for 'master_item_create', allow if they have 'master_item_view'
        if (str_contains($permission, '_')) {
            $parts = explode('_', $permission);
            array_pop($parts);
            $moduleBase = implode('_', $parts);
            if (in_array($moduleBase . '_view', $userPerms)) return true;
            
            // Second level fallback (e.g. master_data_view allows master_item_view)
            if (count($parts) > 1) {
                array_pop($parts);
                $rootBase = implode('_', $parts);
                if (in_array($rootBase . '_view', $userPerms)) return true;
            }
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
