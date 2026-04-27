<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display the Roles and Permissions UI
     */
    public function index()
    {
        return Inertia::render('Permissions/Index', [
            'roles' => Role::with('permissions')
                ->withCount('permissions')
                ->orderBy('name', 'asc')
                ->get(),
            'allPermissions' => Permission::orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Create a new Role
     */
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name|max:255',
        ]);

        Role::create(['name' => strtolower($request->name)]);

        return back()->with('message', 'New Role created successfully!');
    }

    /**
     * Sync Permissions to a specific Role
     */
    public function updatePermissions(Request $request, Role $role)
    {
        // $request->permissions ay array ng strings (e.g., ['edit-voter', 'sync-data'])
        $role->syncPermissions($request->permissions);

        return back()->with('message', "Permissions for {$role->name} updated!");
    }

    /**
     * (Optional) Create a new Permission via API/Console
     */
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|max:255',
        ]);

        Permission::create(['name' => strtolower($request->name)]);

        return back()->with('message', 'New Permission added!');
    }
}