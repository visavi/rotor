<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ChangeCompositeIndexInTables extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('files', function (Blueprint $table) {
            $table->dropIndex('relate_type');
            $table->index(['relate_type', 'relate_id']);
        });

        $this->schema->table('comments', function (Blueprint $table) {
            $table->dropIndex('relate_type');
            $table->index(['relate_type', 'relate_id']);
        });

        $this->schema->table('pollings', function (Blueprint $table) {
            $table->dropIndex('relate_type');
            $table->index(['relate_type', 'relate_id', 'user_id']);
        });

        $this->schema->table('readers', function (Blueprint $table) {
            $table->dropIndex('relate_type');
            $table->index(['relate_type', 'relate_id', 'ip']);
        });

        $this->schema->table('spam', function (Blueprint $table) {
            $table->dropIndex('relate_type');
            $table->index(['relate_type', 'relate_id']);
        });

        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('topic_time');
            $table->dropIndex('user_time');
            $table->dropIndex('rating_time');
            $table->index(['topic_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['rating', 'created_at']);
        });

        $this->schema->table('topics', function (Blueprint $table) {
            $table->dropIndex('count_posts_time');
            $table->dropIndex('user_time');
            $table->dropIndex('forum_time');
            $table->index(['count_posts', 'updated_at']);
            $table->index(['user_id', 'updated_at']);
            $table->index(['forum_id', 'locked', 'updated_at']);
        });

        $this->schema->table('errors', function (Blueprint $table) {
            $table->dropIndex('code');
            $table->index(['code', 'created_at']);
        });

        $this->schema->table('login', function (Blueprint $table) {
            $table->dropIndex('user_time');
            $table->index(['user_id', 'created_at']);
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->index(['user_id', 'author_id']);
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}
