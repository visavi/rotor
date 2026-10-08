<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Главную модуля админ выбирает сам в настройках сайта
        DB::table('settings')->insertOrIgnore([
            ['name' => 'homepage', 'value' => 'feed'],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('name', 'homepage')->delete();
    }
};
