<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('posts')
            ->where('text', 'REGEXP', '\[i\]\[size=1\]Добавлено через [0-9]{2}:[0-9]{2} сек.\[\/size\]\[\/i\]')
            ->update([
                'text' => DB::raw("REGEXP_REPLACE(text, '(\\\\r\\\\n|\\\\n|\\\\r){2}\\\\[i\\\\]\\\\[size=1\\\\]Добавлено через [0-9]{2}:[0-9]{2} сек\\\\.\\\\[\\\\/size\\\\]\\\\[\\\\/i\\\\]', '')"),
            ]);

        // Оставшиеся записи без PHP_EOL
        DB::table('posts')
            ->where('text', 'REGEXP', '\[i\]\[size=1\]Добавлено через [0-9]{2}:[0-9]{2} сек.\[\/size\]\[\/i\]')
            ->update([
                'text' => DB::raw("REGEXP_REPLACE(text, '\\\\[i\\\\]\\\\[size=1\\\\]Добавлено через [0-9]{2}:[0-9]{2} сек\\\\.\\\\[\\\\/size\\\\]\\\\[\\\\/i\\\\]', '')"),
            ]);

        DB::table('posts')
            ->where('text', 'REGEXP', 'Добавлено через [0-9]{2}:[0-9]{2} сек.')
            ->update([
                'text' => DB::raw("REGEXP_REPLACE(text, '(\\\\r\\\\n|\\\\n|\\\\r)Добавлено через [0-9]{2}:[0-9]{2} сек\\\\.', '')"),
            ]);
    }

    public function down(): void
    {
        //
    }
};
