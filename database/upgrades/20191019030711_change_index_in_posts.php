<?php

use Phinx\Migration\AbstractMigration;

class ChangeIndexInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('posts');
        $table
            ->removeIndexByName('topic_time')
            ->addIndex('topic_id')
            ->addIndex('created_at')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table
            ->removeIndexByName('topic_id')
            ->removeIndexByName('created_at')
            ->addIndex(['topic_id', 'created_at'], ['name' => 'topic_time'])
            ->save();
    }
}
