<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = array_filter(
            ['visits', 'allforum', 'allguest', 'allcomments', 'newwall'],
            fn (string $column) => Schema::hasColumn('users', $column)
        );

        if ($columns) {
            Schema::table('users', function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_values($columns));
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['visits', 'allforum', 'allguest', 'allcomments', 'newwall'] as $column) {
                if (! Schema::hasColumn('users', $column)) {
                    $table->integer($column)->default(0);
                }
            }
        });
    }
};
