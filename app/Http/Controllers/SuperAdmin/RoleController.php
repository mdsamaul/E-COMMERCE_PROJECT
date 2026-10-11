<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        return view('superadmin.roles.index', compact('roles'));
    }

    /**
     * Show form to create a new role.
     */
    public function create(): View
    {
        $permissions = Permission::all()->groupBy('module');

        return view('superadmin.roles.create', compact('permissions'));
    }

    /**
     * Store newly created role.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Check uniqueness of generated slug
        if (Role::where('slug', $slug)->exists()) {
            return back()->withInput()->withErrors(['name' => 'A role with this name or slug already exists.']);
        }

        $role = Role::create([
            'name' => trim($validated['name']),
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'role.created',
            'description' => "Created custom role [{$role->name}]",
            'properties' => ['role_id' => $role->id, 'slug' => $role->slug],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')->with('success', "Role [{$role->name}] created successfully.");
    }

    /**
     * Show form to edit role.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::all()->groupBy('module');
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('superadmin.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Update specified role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        // System role slugs cannot be changed
        if (!$role->is_system) {
            $rules['slug'] = ['nullable', 'string', 'max:100', Rule::unique('roles', 'slug')->ignore($role->id)];
        }

        $validated = $request->validate($rules);

        $role->name = trim($validated['name']);
        $role->description = $validated['description'] ?? null;

        if (!$role->is_system && !empty($validated['slug'])) {
            $role->slug = Str::slug($validated['slug']);
        }

        $role->save();

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        } else {
            $role->permissions()->detach();
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'role.updated',
            'description' => "Updated role [{$role->name}]",
            'properties' => ['role_id' => $role->id],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')->with('success', "Role [{$role->name}] updated successfully.");
    }

    /**
     * Delete custom role.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'Protected system roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete role because it is currently assigned to one or more administrators.');
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'role.deleted',
            'description' => "Deleted custom role [{$roleName}]",
            'properties' => ['role_id' => $role->id],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('superadmin.roles.index')->with('success', "Role [{$roleName}] deleted successfully.");
    }
}
