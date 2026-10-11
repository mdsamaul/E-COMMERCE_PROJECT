<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Rules\BangladeshPhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    /**
     * List all administrator accounts.
     */
    public function index(Request $request): View
    {
        $search = trim($request->input('search', ''));
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');

        $query = User::with(['roles', 'directPermissions'])
            ->where(function ($q) {
                $q->where('role', 'admin')->orWhere('is_super_admin', true);
            });

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', $statusFilter === '1');
        }

        if ($roleFilter) {
            if ($roleFilter === 'super-admin') {
                $query->where('is_super_admin', true);
            } else {
                $query->whereHas('roles', function ($q) use ($roleFilter) {
                    $q->where('slug', $roleFilter);
                });
            }
        }

        $admins = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('superadmin.admins.index', compact('admins', 'roles', 'search', 'roleFilter', 'statusFilter'));
    }

    /**
     * Show form to create a new regular Admin.
     */
    public function create(): View
    {
        // Only allow delegating non-superadmin roles
        $roles = Role::where('slug', '!=', 'super-admin')->get();
        $permissions = Permission::where('module', '!=', 'SuperAdmin')->get()->groupBy('module');

        return view('superadmin.admins.create', compact('roles', 'permissions'));
    }

    /**
     * Store newly created Admin account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', new BangladeshPhone(), 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->where('slug', '!=', 'super-admin');
                }),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where(function ($q) {
                $q->where('module', '!=', 'SuperAdmin');
            })],
        ]);

        $normalizedPhone = BangladeshPhone::normalize($validated['phone']);

        $admin = DB::transaction(function () use ($validated, $normalizedPhone, $request) {
            // Force safe attributes: NEVER allow is_super_admin from form
            $user = User::create([
                'name' => trim($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'phone' => $normalizedPhone,
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'is_super_admin' => false,
                'is_active' => (bool) $validated['is_active'],
                'email_verified_at' => now(),
            ]);

            // Assign eligible role
            $user->roles()->sync([$validated['role_id']]);

            // Assign direct permissions if provided
            if (!empty($validated['permissions'])) {
                $user->directPermissions()->sync($validated['permissions']);
            }

            // Log activity
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'admin.created',
                'description' => "Created administrator account [{$user->name}] ({$user->email})",
                'properties' => [
                    'admin_id' => $user->id,
                    'email' => $user->email,
                    'role_id' => $validated['role_id'],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $user;
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', "Administrator [{$admin->name}] created successfully with secure credentials.");
    }

    /**
     * Show form to edit an administrator.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $admin = User::with(['roles', 'directPermissions'])->findOrFail($id);

        // Disallow editing the root super admin through normal edit form if user tries to manipulate
        if ($admin->email === 'admin@shopbd.com' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'The root system Super Administrator cannot be modified.');
        }

        $roles = Role::where('slug', '!=', 'super-admin')->get();
        $permissions = Permission::where('module', '!=', 'SuperAdmin')->get()->groupBy('module');

        return view('superadmin.admins.edit', compact('admin', 'roles', 'permissions'));
    }

    /**
     * Update administrator account details.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $admin = User::findOrFail($id);

        // Security check: Protected Root Super Admin
        if ($admin->email === 'admin@shopbd.com' && $request->user()->id !== $admin->id) {
            abort(403, 'The primary Super Administrator account cannot be modified by another user.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($admin->id)],
            'phone' => ['required', new BangladeshPhone(), Rule::unique('users', 'phone')->ignore($admin->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['required', 'boolean'],
            'role_id' => [
                'nullable',
                'integer',
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->where('slug', '!=', 'super-admin');
                }),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where(function ($q) {
                $q->where('module', '!=', 'SuperAdmin');
            })],
        ]);

        // Prevent deactivating the last active super admin
        if ($admin->isSuperAdmin() && !$validated['is_active']) {
            $activeSuperAdmins = User::where('is_super_admin', true)->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Cannot deactivate the last remaining active Super Administrator.');
            }
        }

        $normalizedPhone = BangladeshPhone::normalize($validated['phone']);

        DB::transaction(function () use ($admin, $validated, $normalizedPhone, $request) {
            $admin->name = trim($validated['name']);
            $admin->email = strtolower(trim($validated['email']));
            $admin->phone = $normalizedPhone;
            $admin->is_active = (bool) $validated['is_active'];

            if (!empty($validated['password'])) {
                $admin->password = Hash::make($validated['password']);
            }

            $admin->save();

            // Update roles if not root superadmin
            if (!$admin->isSuperAdmin() && !empty($validated['role_id'])) {
                $admin->roles()->sync([$validated['role_id']]);
            }

            // Sync direct permissions if not super admin
            if (!$admin->isSuperAdmin()) {
                $admin->directPermissions()->sync($validated['permissions'] ?? []);
            }

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'admin.updated',
                'description' => "Updated administrator account [{$admin->name}] ({$admin->email})",
                'properties' => ['admin_id' => $admin->id],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('superadmin.admins.index')
            ->with('success', "Administrator [{$admin->name}] updated successfully.");
    }

    /**
     * Activate or deactivate an administrator account.
     */
    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $admin = User::findOrFail($id);

        if ($admin->email === 'admin@shopbd.com') {
            return back()->with('error', 'The primary Super Administrator account cannot be deactivated.');
        }

        if ($admin->isSuperAdmin() && $admin->is_active) {
            $activeSuperAdmins = User::where('is_super_admin', true)->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                return back()->with('error', 'Cannot deactivate the only active Super Administrator.');
            }
        }

        $admin->is_active = !$admin->is_active;
        $admin->save();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'admin.status_toggled',
            'description' => ($admin->is_active ? 'Activated' : 'Deactivated') . " administrator [{$admin->name}]",
            'properties' => ['admin_id' => $admin->id, 'is_active' => $admin->is_active],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $statusText = $admin->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Administrator [{$admin->name}] account has been {$statusText}.");
    }

    /**
     * View effective permissions for an administrator.
     */
    public function permissions(int $id): View
    {
        $admin = User::with(['roles.permissions', 'directPermissions'])->findOrFail($id);
        $allPermissions = Permission::all()->groupBy('module');
        $effectivePermissions = $admin->getAllPermissions();

        return view('superadmin.admins.permissions', compact('admin', 'allPermissions', 'effectivePermissions'));
    }

    /**
     * Update authorized role and permissions for an administrator.
     */
    public function updatePermissions(Request $request, int $id): RedirectResponse
    {
        $admin = User::findOrFail($id);

        if ($admin->isSuperAdmin()) {
            return back()->with('error', 'Super Administrator holds unrestricted global system permissions.');
        }

        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(function ($q) {
                    $q->where('slug', '!=', 'super-admin');
                }),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where(function ($q) {
                $q->where('module', '!=', 'SuperAdmin');
            })],
        ]);

        DB::transaction(function () use ($admin, $validated, $request) {
            $admin->roles()->sync([$validated['role_id']]);
            $admin->directPermissions()->sync($validated['permissions'] ?? []);

            ActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'admin.permissions_updated',
                'description' => "Updated permissions for administrator [{$admin->name}]",
                'properties' => ['admin_id' => $admin->id],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()
            ->route('superadmin.admins.permissions', $admin->id)
            ->with('success', "Permissions and role for [{$admin->name}] updated successfully.");
    }
}
