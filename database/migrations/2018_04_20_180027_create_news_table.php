<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('news')) {
            Schema::create('news', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('text');
                $table->integer('user_id');
                $table->integer('count_comments')->default(0);
                $table->boolean('closed')->default(false);
                $table->boolean('top')->default(false);
                $table->integer('rating')->default(0);
                $table->integer('created_at');

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
