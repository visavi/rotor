<?php

use Phinx\Migration\AbstractMigration;

class DeleteForumIdInBookmarks extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('bookmarks');
        $table->removeColumn('forum_id')
            ->removeIndex('forum_id')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('bookmarks');
        $table->addColumn('forum_id', 'integer')
            ->addIndex('forum_id')
            ->save();
    }
}
