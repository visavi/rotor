<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if ($this->hasIndex()) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            // Анкета показывает число комментариев пользователя в каждом разделе,
            // без индекса такой подсчёт сканирует всю таблицу
            $table->index(['user_id', 'relate_type']);
        });
    }

    public function down(): void
    {
        if (! $this->hasIndex()) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'relate_type']);
        });
    }

    private function hasIndex(): bool
    {
        return Schema::hasIndex('comments', ['user_id', 'relate_type']);
    }
};
