<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->integer('user_id');
                $table->string('title');
                $table->string('slug');
                $table->text('text');
                $table->integer('rating')->default(0);
                $table->integer('visits')->default(0);
                $table->integer('count_comments')->default(0);
                $table->integer('created_at');

                $table->index('category_id');
                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
