<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Скрытие рекламы за актив не работало: настройку читал только FAQ
        DB::table('settings')->where('name', 'advertpoint')->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insertOrIgnore(['name' => 'advertpoint', 'value' => 1000]);
    }
};
