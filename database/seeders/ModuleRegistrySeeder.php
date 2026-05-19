<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleRegistrySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('module_registries')->insertOrIgnore([
            'url'        => 'https://github.com/visavi/rotor-modules/releases/download/registry/registry.json',
            'name'       => 'Official Rotor Modules',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
