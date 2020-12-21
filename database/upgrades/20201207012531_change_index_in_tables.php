<?php

declare(strict_types=1);

use App\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

final class ChangeIndexInTables extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->schema->table('logs', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('ban', function (Blueprint $table) {
            $checkIndex = $this->db->getConnection()->select('SHOW INDEXES FROM ban WHERE Key_name="ip"');
            if ($checkIndex) {
                $table->dropUnique('ip');
            }

            $table->unique('ip');
        });

        $this->schema->table('banhist', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('blacklist', function (Blueprint $table) {
            $table->dropIndex('type');
            $table->dropIndex('value');
            $table->index('type');
            $table->index('value');
        });

        $this->schema->table('articles', function (Blueprint $table) {
            $checkIndex = $this->db->getConnection()->select('SHOW INDEXES FROM articles WHERE Key_name="category_id"');
            if ($checkIndex) {
                $table->dropIndex('category_id');
            }
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('category_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('bookmarks', function (Blueprint $table) {
            $table->dropIndex('topic_id');
            $table->dropIndex('user_id');
            $table->index('topic_id');
            $table->index('user_id');
        });

        $this->schema->table('chats', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('comments', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('contacts', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('counters24', function (Blueprint $table) {
            $table->dropUnique('period');
            $table->unique('period');
        });

        $this->schema->table('counters31', function (Blueprint $table) {
            $table->dropUnique('period');
            $table->unique('period');
        });

        $this->schema->table('downs', function (Blueprint $table) {
            $table->dropIndex('category_id');
            $table->dropIndex('created_at');
            $table->index('category_id');
            $table->index('created_at');
        });

        $this->schema->table('files', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('floods', function (Blueprint $table) {
            $table->dropIndex('uid');
            $table->index('uid');
        });

        $this->schema->table('guestbook', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('ignoring', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('invite', function (Blueprint $table) {
            $table->dropIndex('used');
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('used');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('news', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('notes', function (Blueprint $table) {
            $table->dropUnique('user_id');
            $table->unique('user_id');
        });

        $this->schema->table('notebooks', function (Blueprint $table) {
            $table->dropUnique('user_id');
            $table->unique('user_id');
        });

        $this->schema->table('notices', function (Blueprint $table) {
            $table->dropUnique('type');
            $table->unique('type');
        });

        $this->schema->table('offers', function (Blueprint $table) {
            $table->dropIndex('rating');
            $table->dropIndex('created_at');
            $table->index('rating');
            $table->index('created_at');
        });

        $this->schema->table('photos', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('posts', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('rating', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->index('user_id');
        });

        $this->schema->table('stickers', function (Blueprint $table) {
            $table->dropIndex('code');
            $table->index('code');
        });

        $this->schema->table('socials', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->index('user_id');
        });

        $this->schema->table('spam', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });

        $this->schema->table('status', function (Blueprint $table) {
            $table->dropIndex('point');
            $table->dropIndex('topoint');
            $table->index('point');
            $table->index('topoint');
        });

        $this->schema->table('surprise', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->index('user_id');
        });

        $this->schema->table('topics', function (Blueprint $table) {
            $table->dropIndex('updated_at');
            $table->index('updated_at');
        });

        $this->schema->table('transfers', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('recipient_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('recipient_id');
            $table->index('created_at');
        });

        $this->schema->table('users', function (Blueprint $table) {
            $table->dropUnique('login');
            $table->dropUnique('email');
            $table->dropIndex('level');
            $table->dropIndex('point');
            $table->dropIndex('money');
            $table->dropIndex('rating');
            $table->dropIndex('created_at');
            $table->unique('login');
            $table->unique('email');
            $table->index('level');
            $table->index('point');
            $table->index('money');
            $table->index('rating');
            $table->index('created_at');
        });

        $this->schema->table('walls', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex('created_at');
            $table->index('user_id');
            $table->index('created_at');
        });

        $this->schema->table('items', function (Blueprint $table) {
            $table->dropIndex('board_id');
            $table->dropIndex('expires_at');
            $table->dropIndex('created_at');
            $table->index('board_id');
            $table->index('expires_at');
            $table->index('created_at');
        });

        $this->schema->table('messages', function (Blueprint $table) {
            $table->dropIndex('created_at');
            $table->index('created_at');
        });
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}
