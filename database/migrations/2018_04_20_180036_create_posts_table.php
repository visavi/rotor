<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CreatePostsTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('topic_id');
                $table->integer('user_id');
                $table->text('text');
                $table->integer('rating')->default(0);
                $table->ipAddress('ip');
                $table->string('brow', 25);
                $table->integer('edit_user_id')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('created_at');

                $table->index(['topic_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['rating', 'created_at']);
                $table->index('created_at');
                $table->fullText('text');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
