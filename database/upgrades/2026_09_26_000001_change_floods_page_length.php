<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * В колонку пишется путь запроса: /walls/{login}/create с логином
     * в 20 символов длиннее 30, и строгий MySQL такую запись не пропускал
     */
    public function up(): void
    {
        Schema::table('floods', function (Blueprint $table) {
            $table->string('page', 191)->change();
        });
    }

    public function down(): void
    {
        // Состояние флуда временное: длинные пути проще удалить, чем обрезать
        DB::table('floods')->whereRaw('CHAR_LENGTH(page) > 30')->delete();

        Schema::table('floods', function (Blueprint $table) {
            $table->string('page', 30)->change();
        });
    }
};
