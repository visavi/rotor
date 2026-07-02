<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Данные эфемерные (кто сейчас онлайн) — очищаем и меняем тип на месте,
     * вместо построчной конверсии timestamp → datetime.
     */
    public function up(): void
    {
        // Свежая схема уже создаёт updated_at как datetime — конверсия не нужна
        if (Schema::getColumnType('online', 'updated_at') === 'datetime') {
            return;
        }

        DB::table('online')->truncate();
        Schema::table('online', static fn (Blueprint $table) => $table->dateTime('updated_at')->nullable()->change());
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('online', 'updated_at') !== 'datetime') {
            return;
        }

        DB::table('online')->truncate();
        Schema::table('online', static fn (Blueprint $table) => $table->integer('updated_at')->nullable()->change());
    }
};
