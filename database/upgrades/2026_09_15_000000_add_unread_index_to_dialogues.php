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

        Schema::table('dialogues', function (Blueprint $table) {
            // Непрочитанные считаются по диалогам, а не по счётчику в профиле:
            // с этим индексом подсчёт идёт внутри индекса, без походов в таблицу
            $table->index(['user_id', 'reading']);
        });
    }

    public function down(): void
    {
        if (! $this->hasIndex()) {
            return;
        }

        Schema::table('dialogues', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'reading']);
        });
    }

    private function hasIndex(): bool
    {
        return Schema::hasIndex('dialogues', ['user_id', 'reading']);
    }
};
