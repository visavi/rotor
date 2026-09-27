<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Закреплённые записи идут в ленте первыми: при переносе ленты в таблицу feeds
     * сортировка по закреплению потерялась, и закреплённая новость стояла по дате.
     * Флаг у уже закреплённых записей проставится при их пересохранении.
     * Одиночный индекс по created_at заменён составным: лента сортирует по pinned, created_at
     */
    public function up(): void
    {
        if (! Schema::hasColumn('feeds', 'pinned')) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->boolean('pinned')->default(false)->after('relate_id');
                $table->index(['pinned', 'created_at']);
            });
        }

        if (Schema::hasIndex('feeds', ['created_at'])) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->dropIndex(['created_at']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('feeds', ['created_at'])) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->index('created_at');
            });
        }

        if (Schema::hasColumn('feeds', 'pinned')) {
            Schema::table('feeds', function (Blueprint $table) {
                $table->dropIndex(['pinned', 'created_at']);
                $table->dropColumn('pinned');
            });
        }
    }
};
