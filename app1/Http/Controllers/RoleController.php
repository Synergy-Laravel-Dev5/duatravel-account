<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $roles = Role::latest()->get();
        return view('role.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name'       => $request->name,
                'guard_name' => 'web'
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            logUserActivity('Role Created', 'Role: ' . $role->name . ' | Permissions: ' . count($request->permissions ?? []), $role->id, 'Role');

            return redirect()->route('role.index')->with('success', 'Role Created Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $role            = Role::findOrFail($id);
        $permissions     = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('role.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $id
        ]);

        DB::beginTransaction();
        try {
            $role = Role::findOrFail($id);

            $role->update([
                'name'       => $request->name,
                'guard_name' => 'web'
            ]);

            if ($request->has('permissions')) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            logUserActivity('Role Updated', 'Role: ' . $role->name . ' | Permissions: ' . count($request->permissions ?? []), $role->id, 'Role');

            return redirect()->route('role.index')->with('success', 'Role Updated Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        logUserActivity('Role Deleted', 'Role: ' . $role->name, $role->id, 'Role');

        $role->delete();

        return back()->with('success', 'Role Deleted');
    }
}
