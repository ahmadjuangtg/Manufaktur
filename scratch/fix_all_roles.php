<?php
use App\Models\Role;
$roles = Role::all();
foreach ($roles as $role) {
    if (is_array($role->permissions)) {
        $role->permissions = array_values(array_unique($role->permissions));
        $role->save();
    }
}
echo "All role permissions sequentialized.\n";
