<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeIndexInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('topics');

        if ($table->hasIndexByName('last_time')) {
            $table->removeIndexByName('last_time');
        }

        $table
            ->removeIndexByName('forum_id')
            ->removeIndexByName('locked')
            ->addIndex(['count_posts', 'updated_at'], ['name' => 'count_posts_time'])
            ->addIndex(['user_id', 'updated_at'], ['name' => 'user_time'])
            ->addIndex(['forum_id', 'locked', 'updated_at'], ['name' => 'forum_time'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('topics');
        $table
            ->addIndex('forum_id')
            ->addIndex('locked')
            ->removeIndexByName('count_posts_time')
            ->removeIndexByName('user_time')
            ->removeIndexByName('forum_time')
            ->save();
    }
}
