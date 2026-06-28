<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * cached_data хранит JSON реестра доступных модулей — перерос TEXT (64 КБ).
     * Расширяем до LONGTEXT.
     */
    public function up(): void
    {
        Schema::table('module_registries', function (Blueprint $table) {
            $table->longText('cached_data')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('module_registries', function (Blueprint $table) {
            $table->text('cached_data')->nullable()->change();
        });
    }
};
