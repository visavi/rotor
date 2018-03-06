<?php

use Phinx\Migration\AbstractMigration;

class RenamePostsInTopics extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('topics');
        $table->renameColumn('posts', 'count_posts');
    }
}
