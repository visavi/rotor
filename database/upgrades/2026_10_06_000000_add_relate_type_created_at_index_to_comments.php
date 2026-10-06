<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ленты новых комментариев раздела берут последние по дате среди своего типа —
     * без индекса база листает все комментарии по дате и пропускает чужие разделы
     */
    public function up(): void
    {
        if (! Schema::hasIndex('comments', ['relate_type', 'created_at'])) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index(['relate_type', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('comments', ['relate_type', 'created_at'])) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex(['relate_type', 'created_at']);
            });
        }
    }
};
