<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Уникальный индекс остаётся: MySQL допускает сколько угодно NULL в уникальном столбце
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Пустые адреса нельзя вернуть в NOT NULL с уникальным индексом — подставляем заглушку по логину
        DB::table('users')
            ->whereNull('email')
            ->update(['email' => DB::raw("CONCAT(login, '@example.invalid')")]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 100)->nullable(false)->change();
        });
    }
};
