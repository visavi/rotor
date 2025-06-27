<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('confirmregkey', 'confirm_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('confirm_token', 100)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('confirm_token', 'confirmregkey');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('confirm_token', 30)->nullable()->change();
        });
    }
};
