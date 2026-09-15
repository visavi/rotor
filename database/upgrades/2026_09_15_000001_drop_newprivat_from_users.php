<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'newprivat')) {
            return;
        }

        // Денормализованный счётчик расходился с диалогами в обе стороны и чинился
        // только сверху вниз. Теперь непрочитанные считаются по dialogues,
        // а User::newprivat стал вычисляемым свойством
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('newprivat');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'newprivat')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->integer('newprivat')->default(0);
        });
    }
};
