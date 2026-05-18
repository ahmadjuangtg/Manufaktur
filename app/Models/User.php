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
        
        $userPerms = $this->role->permissions;
        if (is_string($userPerms)) $userPerms = json_decode($userPerms, true);
        if (!is_array($userPerms)) $userPerms = [];
        $userPerms = array_map('trim', $userPerms);

        // 1. Super Admin check
        if (in_array('all', $userPerms)) return true;

        // 2. Direct match (High Priority)
        if (in_array($permission, $userPerms)) return true;

        // 3. Strict exclusion: Approval and Security should NEVER fallback
        if (str_contains($permission, 'approval') || str_contains($permission, 'security')) {
            return false;
        }

        // 4. Module-wide view fallback (e.g. master_data_view allowed if has any master_***_view)
        if ($permission === 'master_data_view' || $permission === 'order_view' || $permission === 'production_view') {
            $prefix = str_replace('_view', '', $permission);
            foreach ($userPerms as $p) {
                if (str_starts_with($p, $prefix . '_')) return true;
            }
        }

        // 5. Action fallback: Allow sub-actions if have base view (e.g. master_item_create allowed if has master_item_view)
        // This only applies to standard actions (create, edit, delete, store, update, destroy)
        $standardActions = ['create', 'edit', 'delete', 'store', 'update', 'destroy', 'show'];
        foreach ($standardActions as $action) {
            if (str_contains($permission, '_' . $action)) {
                $baseModule = str_replace('_' . $action, '', $permission);
                if (in_array($baseModule . '_view', $userPerms)) return true;
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
