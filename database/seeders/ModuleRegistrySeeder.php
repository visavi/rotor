<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleRegistrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('modules.default_registries', []) as $registry) {
            DB::table('module_registries')->insertOrIgnore([
                'url'        => $registry['url'],
                'name'       => $registry['name'],
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
