<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Пол больше не спрашивается при регистрации: пустое значение — «не указан».
     * Записанный пол не трогаем — выбранный не отличить от подставленного по умолчанию
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', [User::MALE, User::FEMALE])->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->whereNull('gender')
            ->update(['gender' => User::MALE]);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', [User::MALE, User::FEMALE])->nullable(false)->change();
        });
    }
};
