<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('votes')) {
            return;
        }

        Schema::table('votes', function (Blueprint $table) {
            $table->dropColumn(['closed', 'description']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('votes')) {
            return;
        }

        Schema::table('votes', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->boolean('closed')->default(false)->after('count');
        });
    }
};
