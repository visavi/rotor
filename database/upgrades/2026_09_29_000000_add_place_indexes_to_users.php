<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Подсказки города и страны ищут по началу названия на каждую букву —
     * без индекса каждый запрос читал бы всю таблицу пользователей
     */
    public function up(): void
    {
        foreach (['country', 'city'] as $column) {
            if (! Schema::hasIndex('users', [$column])) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['country', 'city'] as $column) {
            if (Schema::hasIndex('users', [$column])) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropIndex([$column]);
                });
            }
        }
    }
};
