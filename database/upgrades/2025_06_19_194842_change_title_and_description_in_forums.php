<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('forums', function (Blueprint $table) {
            $table->string('title')->change();
            $table->string('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('forums', function (Blueprint $table) {
            $table->string('title', 50)->change();
            $table->string('description', 100)->nullable()->change();
        });
    }
};
