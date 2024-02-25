<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guestbook', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('edit_user_id');

            $table->index(['active', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guestbook', function (Blueprint $table) {
            $table->dropColumn('active');

            $table->dropIndex(['active', 'created_at']);
        });
    }
};
