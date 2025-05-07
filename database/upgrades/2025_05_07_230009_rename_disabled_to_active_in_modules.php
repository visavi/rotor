<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('active')
                ->default(true)
                ->after('disabled');
        });

        DB::statement('UPDATE modules SET active = NOT disabled');

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('disabled');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('disabled')
                ->default(false)
                ->after('active');
        });

        DB::statement('UPDATE modules SET disabled = NOT active');

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
};
