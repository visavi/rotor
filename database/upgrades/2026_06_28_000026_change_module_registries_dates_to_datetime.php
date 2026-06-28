<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Свежая схема уже создаёт колонки как datetime — конверсия не нужна
        if (Schema::getColumnType('module_registries', 'created_at') === 'datetime') {
            return;
        }

        // timestamp и datetime отдают одинаковый литерал 'Y-m-d H:i:s' при стабильной
        // session-tz, поэтому смена типа сохраняет значения без сдвига
        Schema::table('module_registries', function (Blueprint $table) {
            $table->dateTime('cached_at')->nullable()->change();
            $table->dateTime('created_at')->nullable()->change();
            $table->dateTime('updated_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getColumnType('module_registries', 'created_at') !== 'datetime') {
            return;
        }

        Schema::table('module_registries', function (Blueprint $table) {
            $table->timestamp('cached_at')->nullable()->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });
    }
};
