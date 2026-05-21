<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::latest()->paginate(50);
        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'Permission name must contain only lowercase letters, numbers, hyphens, and underscores.',
        ]);

        Permission::create([
            'name' => $request->name,
            'description' => $request->description ?? '',
        ]);

        return redirect()->route('admin.permissions.index')
                         ->with('success', 'Permission created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id . '|regex:/^[a-z0-9_-]+$/',
            'description' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'Permission name must contain only lowercase letters, numbers, hyphens, and underscores.',
        ]);

        $permission->update([
            'name' => $request->name,
            'description' => $request->description ?? '',
        ]);

        return redirect()->route('admin.permissions.index')
                         ->with('success', 'Permission updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to any role
        if ($permission->roles()->exists()) {
            return redirect()->route('admin.permissions.index')
                           ->with('error', 'Cannot delete permission that is assigned to roles!');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
                         ->with('success', 'Permission deleted successfully!');
    }
}
