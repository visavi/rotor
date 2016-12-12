<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('posts');
        $table->removeIndexByName('posts_forums_id');
        $table->removeIndexByName('posts_text');
        $table->removeIndexByName('posts_topics_id');
        $table->removeIndexByName('posts_user');
        $table->addIndex('forum_id')
            ->addIndex('text', ['type' => 'fulltext'])
            ->addIndex(['topic_id', 'time'], ['name' => 'topic_time'])
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table->removeIndexByName('forum_id');
        $table->removeIndexByName('text');
        $table->removeIndexByName('topic_time');
        $table->removeIndexByName('user');
        $table->addIndex('forum_id', ['name' => 'posts_forums_id'])
            ->addIndex('text', ['type' => 'fulltext', 'name' => 'posts_text'])
            ->addIndex(['topic_id', 'time'], ['name' => 'posts_topics_id'])
            ->addIndex('user', ['name' => 'posts_user'])
            ->save();
    }
}
