<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * created_at был TIMESTAMP (4 байта, 1970-2038, UTC-сдвиг сессии). Приводим к
     * DATETIME — единый тип дат во всём проекте. Токены эфемерны (живут 1 час,
     * чистятся), поэтому конверсия данных не нужна — просто меняем тип.
     */
    public function up(): void
    {
        if (Schema::getColumnType('email_changes', 'created_at') === 'datetime') {
            return;
        }

        Schema::table('email_changes', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getColumnType('email_changes', 'created_at') !== 'datetime') {
            return;
        }

        Schema::table('email_changes', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
        });
    }
};
