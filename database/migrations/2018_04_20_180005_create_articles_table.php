<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CreateArticlesTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('category_id');
                $table->integer('user_id');
                $table->string('title', 50);
                $table->text('text');
                $table->string('tags', 100);
                $table->integer('rating')->default(0);
                $table->integer('visits')->default(0);
                $table->integer('count_comments')->default(0);
                $table->integer('created_at');

                $table->index('category_id');
                $table->index('user_id');
                $table->index('created_at');
            });

            if (config('database.default') === 'mysql') {
                DB::statement('CREATE FULLTEXT INDEX articles_title_text_fulltext ON articles(title, text);');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
}
