<?php

use Phinx\Migration\AbstractMigration;

class DeleteForumIdInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('posts');
        $table->removeColumn('forum_id')
            ->removeIndex('forum_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('posts');
        $table->addColumn('forum_id', 'integer')
            ->addIndex('forum_id')
            ->save();
    }
}
