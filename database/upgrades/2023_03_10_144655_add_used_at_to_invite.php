<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invite', function (Blueprint $table) {
            $table->integer('used_at')->after('used')->nullable();

            $table->index(['user_id', 'used', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invite', function (Blueprint $table) {
            $table->dropColumn('used_at');

            $table->dropIndex(['user_id', 'used', 'created_at']);
        });
    }
};
