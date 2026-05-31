<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['visits', 'allforum', 'allguest', 'allcomments', 'newwall']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('visits')->default(0);
            $table->integer('allforum')->default(0);
            $table->integer('allguest')->default(0);
            $table->integer('allcomments')->default(0);
            $table->integer('newwall')->default(0);
        });
    }
};
