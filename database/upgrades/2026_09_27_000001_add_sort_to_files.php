<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ручной порядок вложений: картинки в форме перетаскивают, и порядок
     * сохраняется. У старых файлов 0 — они остаются в порядке загрузки
     */
    public function up(): void
    {
        if (! Schema::hasColumn('files', 'sort')) {
            Schema::table('files', function (Blueprint $table) {
                $table->unsignedSmallInteger('sort')->default(0)->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('files', 'sort')) {
            Schema::table('files', function (Blueprint $table) {
                $table->dropColumn('sort');
            });
        }
    }
};
