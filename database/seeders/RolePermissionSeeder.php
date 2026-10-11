<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Defined System Permissions by Module
        $permissionsByModule = [
            'Catalog' => [
                ['name' => 'View Products', 'slug' => 'products.view', 'description' => 'Can view product list and details'],
                ['name' => 'Create Products', 'slug' => 'products.create', 'description' => 'Can create new products and gallery images'],
                ['name' => 'Edit Products', 'slug' => 'products.edit', 'description' => 'Can update product information and prices'],
                ['name' => 'Delete Products', 'slug' => 'products.delete', 'description' => 'Can delete products'],
                ['name' => 'Manage Inventory', 'slug' => 'products.stock', 'description' => 'Can view stock and adjust inventory levels'],
                ['name' => 'Manage Categories', 'slug' => 'categories.manage', 'description' => 'Can create, edit, and delete product categories'],
                ['name' => 'Manage Brands', 'slug' => 'brands.manage', 'description' => 'Can create, edit, and delete product brands'],
            ],
            'Orders' => [
                ['name' => 'View Orders', 'slug' => 'orders.view', 'description' => 'Can view orders list and details'],
                ['name' => 'Update Order Status', 'slug' => 'orders.update_status', 'description' => 'Can change order processing status'],
                ['name' => 'Update Payment Status', 'slug' => 'orders.update_payment', 'description' => 'Can record or update payment transactions'],
                ['name' => 'Dispatch Courier', 'slug' => 'orders.dispatch', 'description' => 'Can dispatch orders to Steadfast/Pathao courier'],
                ['name' => 'Generate Invoices', 'slug' => 'orders.invoice', 'description' => 'Can view and download customer invoices'],
            ],
            'Marketing' => [
                ['name' => 'Manage Coupons', 'slug' => 'coupons.manage', 'description' => 'Can create, edit, and delete discount coupons'],
                ['name' => 'Manage Banners', 'slug' => 'banners.manage', 'description' => 'Can upload and arrange homepage promotional banners'],
                ['name' => 'Moderate Reviews', 'slug' => 'reviews.manage', 'description' => 'Can approve, reject, or delete customer reviews'],
            ],
            'Customers' => [
                ['name' => 'View Customers', 'slug' => 'customers.view', 'description' => 'Can view registered customer directory and orders'],
                ['name' => 'Toggle Customer Status', 'slug' => 'customers.toggle_status', 'description' => 'Can activate or deactivate customer accounts'],
            ],
            'Reports' => [
                ['name' => 'View Sales Reports', 'slug' => 'reports.sales', 'description' => 'Can view financial and sales analytics'],
            ],
            'Settings' => [
                ['name' => 'Manage Store Settings', 'slug' => 'settings.manage', 'description' => 'Can update store info, delivery charges, and gateways'],
            ],
            'SuperAdmin' => [
                ['name' => 'Manage Administrators', 'slug' => 'admins.manage', 'description' => 'Can create, edit, and manage regular admin accounts'],
                ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Can create, configure, and assign roles'],
                ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'description' => 'Can view and inspect system permissions'],
                ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'Can view security audit trail and logs'],
            ],
        ];

        $allPermissionIds = [];
        $adminPermissionSlugs = [];

        foreach ($permissionsByModule as $module => $permissions) {
            foreach ($permissions as $p) {
                $permission = Permission::updateOrCreate(
                    ['slug' => $p['slug']],
                    [
                        'name' => $p['name'],
                        'module' => $module,
                        'description' => $p['description'],
                    ]
                );

                $allPermissionIds[] = $permission->id;

                // Non-superadmin modules belong to standard admin role
                if ($module !== 'SuperAdmin') {
                    $adminPermissionSlugs[] = $permission->slug;
                }
            }
        }

        // 2. System Roles
        // A. Super Admin Role
        $superAdminRole = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Has supreme authority over the entire platform, administrative staff, and system configuration.',
                'is_system' => true,
            ]
        );
        $superAdminRole->permissions()->sync($allPermissionIds);

        // B. General Admin Role
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'General Administrator',
                'description' => 'Can manage day-to-day operations: catalog, orders, stock, marketing, and customers.',
                'is_system' => true,
            ]
        );
        $adminRolePermissionIds = Permission::whereIn('slug', $adminPermissionSlugs)->pluck('id')->toArray();
        $adminRole->permissions()->sync($adminRolePermissionIds);

        // C. Order Manager Role
        $orderManagerRole = Role::updateOrCreate(
            ['slug' => 'order-manager'],
            [
                'name' => 'Order & Logistics Manager',
                'description' => 'Specialized role for processing orders, updating statuses, dispatching couriers, and viewing stock.',
                'is_system' => false,
            ]
        );
        $orderManagerPermissions = Permission::whereIn('slug', [
            'orders.view', 'orders.update_status', 'orders.update_payment', 'orders.dispatch', 'orders.invoice',
            'products.view', 'products.stock', 'customers.view',
        ])->pluck('id')->toArray();
        $orderManagerRole->permissions()->sync($orderManagerPermissions);

        // D. Catalog Manager Role
        $catalogManagerRole = Role::updateOrCreate(
            ['slug' => 'catalog-manager'],
            [
                'name' => 'Catalog & Inventory Specialist',
                'description' => 'Specialized role for adding and managing products, brands, categories, and inventory stock.',
                'is_system' => false,
            ]
        );
        $catalogManagerPermissions = Permission::whereIn('slug', [
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.stock',
            'categories.manage', 'brands.manage', 'reviews.manage',
        ])->pluck('id')->toArray();
        $catalogManagerRole->permissions()->sync($catalogManagerPermissions);

        // 3. Guarantee Default Super Admin Account has is_super_admin = true and super-admin role
        $superAdminUser = User::where('email', 'admin@shopbd.com')->first();
        if ($superAdminUser) {
            $superAdminUser->update([
                'is_super_admin' => true,
                'role' => 'admin',
                'is_active' => true,
            ]);
            $superAdminUser->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
