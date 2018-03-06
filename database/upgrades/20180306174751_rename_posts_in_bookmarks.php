<?php

use Phinx\Migration\AbstractMigration;

class RenamePostsInBookmarks extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('bookmarks');
        $table->renameColumn('posts', 'count_posts');
    }
}
