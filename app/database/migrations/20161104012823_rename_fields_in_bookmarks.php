<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBookmarks extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('bookmarks');
        $table->renameColumn('book_id', 'id');
        $table->renameColumn('book_user', 'user');
        $table->renameColumn('book_topic', 'topic');
        $table->renameColumn('book_forum', 'forum');
        $table->renameColumn('book_posts', 'posts');
    }
}
