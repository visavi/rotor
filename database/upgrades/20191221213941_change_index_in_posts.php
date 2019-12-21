<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeIndexInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('posts');
        $table
            ->removeIndexByName('user_id')
            ->addIndex(['user_id', 'created_at'], ['name' => 'user_time'])
            ->addIndex(['rating', 'created_at'], ['name' => 'rating_time'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('posts');
        $table
            ->addIndex('user_id')
            ->removeIndexByName('user_time')
            ->removeIndexByName('rating_time')
            ->save();
    }
}
