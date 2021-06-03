<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class CreateTopicsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('topics')) {
            Schema::create('topics', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('forum_id');
                $table->string('title', 50);
                $table->integer('user_id');
                $table->boolean('closed')->default(false);
                $table->boolean('locked')->default(false);
                $table->string('moderators')->nullable();
                $table->string('note')->nullable();
                $table->integer('count_posts');
                $table->integer('visits')->default(0);
                $table->integer('last_post_id')->nullable();
                $table->integer('close_user_id')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index(['count_posts', 'updated_at']);
                $table->index(['user_id', 'updated_at']);
                $table->index(['forum_id', 'locked', 'updated_at']);
                $table->index('updated_at');
            });

            if (config('database.default') === 'mysql') {
                $this->db->getConnection()->statement('CREATE FULLTEXT INDEX text ON topics(title);');
            }
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
}
