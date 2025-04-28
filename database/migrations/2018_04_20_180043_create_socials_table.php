<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('socials')) {
            Schema::create('socials', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('network');
                $table->string('uid');
                $table->integer('created_at');

                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('socials');
    }
};
