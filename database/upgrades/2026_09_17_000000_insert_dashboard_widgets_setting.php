<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Пустая строка означает «показывать все»: после обновления панель
        // выглядит как раньше, пока админ не зайдёт в настройки виджетов
        DB::table('settings')->insertOrIgnore([
            ['name' => 'dashboard_widgets', 'value' => ''],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('name', 'dashboard_widgets')->delete();
    }
};
