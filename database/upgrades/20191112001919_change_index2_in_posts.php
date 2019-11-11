<?php

use Phinx\Migration\AbstractMigration;

class ChangeIndex2InPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('posts');
        $table
            ->removeIndexByName('topic_id')
            ->addIndex(['topic_id', 'created_at'], ['name' => 'topic_time'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table
            ->removeIndexByName('topic_time')
            ->addIndex('topic_id')
            ->save();
    }
}
