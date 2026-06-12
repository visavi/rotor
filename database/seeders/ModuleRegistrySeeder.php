<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleRegistrySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('module_registries')->insertOrIgnore([
            'url'        => config('modules.default_registry.url'),
            'name'       => config('modules.default_registry.name'),
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
