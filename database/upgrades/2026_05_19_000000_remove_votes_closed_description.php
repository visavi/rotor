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

        $columns = array_filter(
            ['closed', 'description'],
            fn (string $column) => Schema::hasColumn('votes', $column)
        );

        if ($columns) {
            Schema::table('votes', function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_values($columns));
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('votes')) {
            return;
        }

        Schema::table('votes', function (Blueprint $table) {
            if (! Schema::hasColumn('votes', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('votes', 'closed')) {
                $table->boolean('closed')->default(false)->after('count');
            }
        });
    }
};
