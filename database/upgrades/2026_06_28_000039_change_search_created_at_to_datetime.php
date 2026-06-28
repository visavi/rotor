<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Свежая схема уже создаёт created_at как datetime — менять нечего
        if (Schema::getColumnType('search', 'created_at') === 'datetime') {
            return;
        }

        // Индекс поиска перегенерируем: чистим таблицу, на пустой смена типа мгновенна.
        // Переиндексация: php artisan search:import
        DB::table('search')->truncate();

        Schema::table('search', function (Blueprint $table) {
            $table->dropIndex(['relate_type', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('search', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable();
            $table->index(['relate_type', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        // Колонка уже int — откатывать нечего
        if (Schema::getColumnType('search', 'created_at') !== 'datetime') {
            return;
        }

        DB::table('search')->truncate();

        Schema::table('search', function (Blueprint $table) {
            $table->dropIndex(['relate_type', 'created_at']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('created_at');
        });
        Schema::table('search', function (Blueprint $table) {
            $table->integer('created_at')->nullable();
            $table->index(['relate_type', 'created_at']);
            $table->index('created_at');
        });
    }
};
