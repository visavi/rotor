<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            ! Schema::hasTable('module_registries')
            || Schema::hasIndex('module_registries', ['url'], 'unique')
        ) {
            return;
        }

        Schema::table('module_registries', function (Blueprint $table) {
            $table->unique('url');
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('module_registries')
            || ! Schema::hasIndex('module_registries', ['url'], 'unique')
        ) {
            return;
        }

        Schema::table('module_registries', function (Blueprint $table) {
            $table->dropUnique(['url']);
        });
    }
};
