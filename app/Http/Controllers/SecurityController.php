<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecurityController extends Controller
{
    // Roles
    public function indexRole()
    {
        // dd(auth()->user()->hasPermission('security_view'));
        $data = Role::all();
        return view('security.roles.index', compact('data'));
    }

    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required']);
        Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);
        return redirect()->back()->with('success', 'Role berhasil ditambahkan');
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Administrator') {
            return redirect()->back()->with('error', 'Super Administrator tidak dapat diubah');
        }

        $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions ?? [],
        ]);
        return redirect()->back()->with('success', 'Role berhasil diperbarui');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'Super Administrator') {
            return redirect()->back()->with('error', 'Super Administrator tidak dapat dihapus');
        }

        $role->delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus');
    }

    // Accounts / Users
    public function indexAccount()
    {
        $data = User::with(['role', 'warehouses'])->get();
        $roles = Role::all();
        $warehouses = Warehouse::all();
        return view('security.accounts.index', compact('data', 'roles', 'warehouses'));
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role_id' => 'required',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        if ($request->has('warehouse_ids')) {
            $user->warehouses()->sync($request->warehouse_ids);
        }

        return redirect()->back()->with('success', 'User berhasil ditambahkan');
    }

    public function updateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'role_id' => 'required',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('warehouse_ids')) {
            $user->warehouses()->sync($request->warehouse_ids);
        } else {
            $user->warehouses()->detach();
        }

        return redirect()->back()->with('success', 'User berhasil diperbarui');
    }

    public function destroyAccount($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role && $user->role->name === 'Super Administrator') {
            return redirect()->back()->with('error', 'Akun Super Administrator tidak dapat dihapus');
        }

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus');
    }
}
