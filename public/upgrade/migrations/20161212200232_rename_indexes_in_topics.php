<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('topics');
        $table->removeIndexByName('topics_forums_id');
        $table->removeIndexByName('topics_last_time');
        $table->removeIndexByName('topics_locked');
        $table->removeIndexByName('topics_title');
        $table->addIndex('forum_id')
            ->addIndex('last_time')
            ->addIndex('locked')
            ->addIndex('title', ['type' => 'fulltext'])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('topics');
        $table->removeIndexByName('forum_id');
        $table->removeIndexByName('last_time');
        $table->removeIndexByName('locked');
        $table->removeIndexByName('title');
        $table->addIndex('forum_id', ['name' => 'topics_forums_id'])
            ->addIndex('last_time', ['name' => 'topics_last_time'])
            ->addIndex('locked', ['name' => 'topics_locked'])
            ->addIndex('title', ['type' => 'fulltext', 'name' => 'topics_title'])
            ->save();
    }
}
