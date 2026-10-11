<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_admins' => User::where('role', 'admin')->where('is_super_admin', false)->count(),
            'total_superadmins' => User::where('is_super_admin', true)->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_orders' => Order::count(),
            'total_revenue' => (float) Order::where('payment_status', 'paid')->sum('total_amount'),
            'total_products' => Product::count(),
        ];

        $recentAdmins = User::where('role', 'admin')
            ->with('roles')
            ->latest()
            ->take(5)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        $systemRoles = Role::withCount('users', 'permissions')->get();

        return view('superadmin.dashboard', compact('stats', 'recentAdmins', 'recentLogs', 'systemRoles'));
    }
}
