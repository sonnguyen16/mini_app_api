<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Tạo permissions
        $permissions = [
            // App management
            'view-apps',
            'create-apps',
            'edit-apps',
            'delete-apps',
            
            // User management
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            
            // Category management
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            
            // Voucher management
            'view-vouchers',
            'create-vouchers',
            'edit-vouchers',
            'delete-vouchers',
            
            // Points management
            'view-points',
            'add-points',
            'subtract-points',
            
            // Transaction management
            'view-transactions',
            'create-transactions',
            
            // Policy management
            'view-policies',
            'edit-policies',
            
            // Wallet management
            'view-wallet',
            'redeem-voucher',
            'use-voucher',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Tạo roles
        $adminRole = Role::create(['name' => 'admin']);
        $appOwnerRole = Role::create(['name' => 'app_owner']);
        $endUserRole = Role::create(['name' => 'end_user']);

        // Gán permissions cho admin (có tất cả quyền)
        $adminRole->givePermissionTo(Permission::all());

        // Gán permissions cho app_owner
        $appOwnerPermissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            'view-vouchers', 'create-vouchers', 'edit-vouchers', 'delete-vouchers',
            'view-points', 'add-points', 'subtract-points',
            'view-transactions', 'create-transactions',
            'view-policies', 'edit-policies',
            'view-wallet', 'redeem-voucher', 'use-voucher',
        ];
        $appOwnerRole->givePermissionTo($appOwnerPermissions);

        // Gán permissions cho end_user (chỉ API mobile)
        $endUserPermissions = [
            'view-wallet',
            'redeem-voucher',
            'use-voucher',
        ];
        $endUserRole->givePermissionTo($endUserPermissions);
    }
}
