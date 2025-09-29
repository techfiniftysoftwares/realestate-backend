<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;

class UserManagementSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $module = Module::create([
                'name' => 'User Management',
                'is_active' => 1,
            ]);

            $submodules = [
                ['title' => 'Users', 'is_active' => 1],
                ['title' => 'User Roles', 'is_active' => 1]
            ];

            foreach ($submodules as $submoduleData) {
                $submoduleData['module_id'] = $module->id;
                Submodule::create($submoduleData);
            }

            DB::commit();
            $this->command->info('User Management module seeded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
