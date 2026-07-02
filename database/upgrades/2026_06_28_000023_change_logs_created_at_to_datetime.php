<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Таблица большая, а данные расходные — очищаем и меняем тип на месте,
     * вместо построчной конверсии timestamp → datetime.
     */
    public function up(): void
    {
        // Свежая схема уже создаёт created_at как datetime — конверсия не нужна
        if (Schema::getColumnType('logs', 'created_at') === 'datetime') {
            return;
        }

        DB::table('logs')->truncate();
        Schema::table('logs', static fn (Blueprint $table) => $table->dateTime('created_at')->nullable()->change());
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('logs', 'created_at') !== 'datetime') {
            return;
        }

        DB::table('logs')->truncate();
        Schema::table('logs', static fn (Blueprint $table) => $table->integer('created_at')->nullable()->change());
    }
};