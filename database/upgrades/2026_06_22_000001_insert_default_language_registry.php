<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('module_registries')->insertOrIgnore([
            'url'        => 'https://github.com/visavi/rotor-languages/releases/download/registry/registry.json',
            'name'       => 'Official Rotor Languages',
            'active'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('module_registries')
            ->where('url', 'https://github.com/visavi/rotor-languages/releases/download/registry/registry.json')
            ->delete();
    }
};
