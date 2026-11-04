<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Стикеров на странице по умолчанию 24 вместо 20: в ряду их 3, 4 или 6, и 20 обрывало последний ряд.
     * Своё число владельца не трогаем
     */
    public function up(): void
    {
        DB::table('settings')->where('name', 'stickerlist')->where('value', '20')->update(['value' => 24]);
    }

    public function down(): void
    {
        DB::table('settings')->where('name', 'stickerlist')->where('value', '24')->update(['value' => 20]);
    }
};
