<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('settings')->whereIn('name', ['homepage_view', 'ziplist'])->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insertOrIgnore([
            ['name' => 'homepage_view', 'value' => 'feed'],
            ['name' => 'ziplist', 'value' => 50],
        ]);
    }
};
