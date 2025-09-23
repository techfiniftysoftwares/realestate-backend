<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Submodule;
use Illuminate\Support\Facades\DB;

class ModulesAndSubmodulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Define the modules and their submodules
            $modulesData = [
                [
                    'name' => 'Properties Management',


                    'is_active' => 1,
                    'submodules' => [
                        [
                            'title' => 'Properties',
                            'is_active' => 1
                        ],
                        [
                            'title' => 'Property Types',

                            'is_active' => 1
                        ]
                    ]
                ],
                [
                    'name' => 'Blog Management',


                    'is_active' => 1,
                    'submodules' => [
                        [
                            'title' => 'Blogs',
                            'is_active' => 1
                        ],
                        [
                            'title' => 'Categories',
                            'is_active' => 1
                        ],
                        [
                            'title' => 'Comments',
                            'is_active' => 1
                        ]
                    ]
                ],
                [
                    'name' => 'Services Management',


                    'is_active' => 1,
                    'submodules' => [
                        [
                            'title' => 'Services List',
                            'is_active' => 1
                        ]
                    ]
                ],
                [
                    'name' => 'Testimonial Management',


                    'is_active' => 1,
                    'submodules' => [
                        [
                            'title' => 'Testimonies',
                            'is_active' => 1
                        ]
                    ]
                ]
            ];

            // Create modules and their submodules
            foreach ($modulesData as $moduleData) {
                // Extract submodules data
                $submodulesData = $moduleData['submodules'];
                unset($moduleData['submodules']);

                // Create the module
                $module = Module::create($moduleData);

                // Create submodules for this module
                foreach ($submodulesData as $submoduleData) {
                    $submoduleData['module_id'] = $module->id;
                    Submodule::create($submoduleData);
                }

                $this->command->info("Created module: {$module->name} with " . count($submodulesData) . " submodules");
            }

            DB::commit();
            $this->command->info('Modules and submodules seeded successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding modules and submodules: ' . $e->getMessage());
            throw $e;
        }
    }
}
