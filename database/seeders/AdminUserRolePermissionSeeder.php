<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Module;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Create Super Admin Role
            $superAdminRole = Role::create([
                'name' => 'Super Admin',
            ]);

            $this->command->info("Created role: {$superAdminRole->name}");

            // 2. Get all modules and submodules
            $modules = Module::with('submodules')->where('is_active', 1)->get();

            if ($modules->isEmpty()) {
                throw new \Exception('No modules found. Please run ModulesAndSubmodulesSeeder first.');
            }

            // 3. Create all permissions for all modules/submodules
            $actions = ['create', 'read', 'update', 'delete'];
            $createdPermissions = [];

            foreach ($modules as $module) {
                $this->command->info("Processing module: {$module->name}");

                foreach ($module->submodules as $submodule) {
                    $this->command->info("  Processing submodule: {$submodule->title}");

                    foreach ($actions as $action) {
                        $permission = Permission::firstOrCreate([
                            'module_id' => $module->id,
                            'submodule_id' => $submodule->id,
                            'action' => $action,
                        ]);

                        $createdPermissions[] = $permission->id;
                        $this->command->info("    Created permission: {$permission->action} for {$submodule->title}");
                    }
                }
            }

            // 4. Attach all permissions to Super Admin role
            $superAdminRole->permissions()->attach($createdPermissions);
            $this->command->info("Attached " . count($createdPermissions) . " permissions to Super Admin role");

            // 5. Create Super Admin User
            $superAdminUser = User::create([
                'name' => 'Super Administrator',
                'email' => 'admin@realestate.com',
                'password' => Hash::make('admin123'), // Change this to a secure password
                'phone' => '+254700000000',
                'role_id' => $superAdminRole->id,
                'is_active' => 1,
            ]);

            $this->command->info("Created Super Admin user: {$superAdminUser->email}");

            // 6. Create additional basic roles (optional)
            $managerRole = Role::create([
                'name' => 'Property Manager',
            ]);

            $editorRole = Role::create([
                'name' => 'Content Editor',
            ]);

            // Assign specific permissions to Property Manager (Properties and Services)
            $propertyManagerPermissions = Permission::whereHas('module', function($query) {
                $query->whereIn('name', ['Properties Management', 'Services Management']);
            })->pluck('id')->toArray();

            $managerRole->permissions()->attach($propertyManagerPermissions);
            $this->command->info("Created Property Manager role with " . count($propertyManagerPermissions) . " permissions");

            // Assign specific permissions to Content Editor (Blog and Testimonials)
            $editorPermissions = Permission::whereHas('module', function($query) {
                $query->whereIn('name', ['Blog Management', 'Testimonial Management']);
            })->pluck('id')->toArray();

            $editorRole->permissions()->attach($editorPermissions);
            $this->command->info("Created Content Editor role with " . count($editorPermissions) . " permissions");

            // 7. Create sample users for other roles
            $propertyManager = User::create([
                'name' => 'John Manager',
                'email' => 'manager@realestate.com',
                'password' => Hash::make('manager123'),
                'phone' => '+254700000001',
                'role_id' => $managerRole->id,
                'is_active' => 1,
            ]);

            $contentEditor = User::create([
                'name' => 'Jane Editor',
                'email' => 'editor@realestate.com',
                'password' => Hash::make('editor123'),
                'phone' => '+254700000002',
                'role_id' => $editorRole->id,
                'is_active' => 1,
            ]);

            $this->command->info("Created Property Manager user: {$propertyManager->email}");
            $this->command->info("Created Content Editor user: {$contentEditor->email}");

            DB::commit();

            $this->command->info('=================================');
            $this->command->info('SUCCESS: Admin user, roles, and permissions seeded successfully!');
            $this->command->info('=================================');
            $this->command->info('Login Credentials:');
            $this->command->info('');
            $this->command->info('Super Admin:');
            $this->command->info('Email: admin@realestate.com');
            $this->command->info('Password: admin123');
            $this->command->info('');
            $this->command->info('Property Manager:');
            $this->command->info('Email: manager@realestate.com');
            $this->command->info('Password: manager123');
            $this->command->info('');
            $this->command->info('Content Editor:');
            $this->command->info('Email: editor@realestate.com');
            $this->command->info('Password: editor123');
            $this->command->info('');
            $this->command->warn('IMPORTANT: Please change all passwords after first login!');
            $this->command->info('=================================');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding admin data: ' . $e->getMessage());
            throw $e;
        }
    }
}
