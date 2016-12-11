<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInBookmarks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bookmarks');
        $table->removeIndexByName('book_forum');
        $table->removeIndexByName('book_topic');
        $table->removeIndexByName('book_user');
        $table->addIndex('forum_id')
            ->addIndex('topic_id')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('bookmarks');
        $table->removeIndexByName('forum_id');
        $table->removeIndexByName('topic_id');
        $table->removeIndexByName('user');
        $table->addIndex('forum_id', ['name' => 'book_forum'])
            ->addIndex('topic_id', ['name' => 'book_topic'])
            ->addIndex('user', ['name' => 'book_user'])
            ->save();
    }
}
