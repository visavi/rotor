<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('errors')) {
            Schema::create('errors', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('code');
                $table->string('request')->nullable();
                $table->string('referer')->nullable();
                $table->integer('user_id')->nullable();
                $table->string('message')->nullable();
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('created_at');

                $table->index(['code', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('errors');
    }
};
