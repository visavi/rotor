<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * У разбана нет причины: запись шла без reason и в строгом MySQL падала.
     * Колонка становится nullable — text без DEFAULT, поэтому годится и для MySQL до 8.0.13
     */
    public function up(): void
    {
        Schema::table('banhist', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
        });

        DB::table('banhist')
            ->where('type', 'unban')
            ->where('reason', '')
            ->update(['reason' => null]);
    }

    public function down(): void
    {
        DB::table('banhist')
            ->whereNull('reason')
            ->update(['reason' => '']);

        Schema::table('banhist', function (Blueprint $table) {
            $table->text('reason')->nullable(false)->change();
        });
    }
};
