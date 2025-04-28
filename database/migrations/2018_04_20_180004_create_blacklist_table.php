<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('blacklist')) {
            Schema::create('blacklist', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type', 30);
                $table->string('value', 100);
                $table->integer('user_id');
                $table->integer('created_at');

                $table->index('type');
                $table->index('value');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklist');
    }
};
