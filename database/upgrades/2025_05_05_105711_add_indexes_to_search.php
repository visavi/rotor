<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('search', function (Blueprint $table) {
            $table->index(['relate_type', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('search', function (Blueprint $table) {
            $table->dropIndex(['relate_type', 'created_at']);
            $table->dropIndex('created_at');
        });
    }
};
