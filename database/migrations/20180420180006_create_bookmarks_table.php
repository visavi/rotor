<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class CreateBookmarksTable extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        if (! $this->schema->hasTable('bookmarks')) {
            $this->schema->create('bookmarks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('topic_id');
                $table->integer('count_posts');

                $table->index('topic_id');
                $table->index('user_id');
            });
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->schema->dropIfExists('bookmarks');
    }
}
