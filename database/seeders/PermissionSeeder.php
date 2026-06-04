<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // ====== CREATE PERMISSIONS ======
        $permissions = [
            'view_dashboard',
            'view_classes',
            'view_students',
            'view_assessment',
            'view_analysis',
            'view_reports',
            'manage_users',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Clear cache after creating permissions
        app()['cache']->forget('spatie.permission.cache');

        // ====== CREATE ROLES & ASSIGN PERMISSIONS ======
        
        // Guru Role
        $guruRole = Role::firstOrCreate(['name' => 'guru']);
        $guruRole->syncPermissions([
            'view_dashboard',
            'view_classes',
            'view_students',
            'view_assessment',
            'view_analysis',
            'view_reports',
        ]);

        // Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions([
            'view_dashboard',
            'manage_users',
            'manage_settings',
        ]);

        // Clear cache after assigning permissions
        app()['cache']->forget('spatie.permission.cache');
    }
}
