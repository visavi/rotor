<?php

use Phinx\Migration\AbstractMigration;

class RenameCommentsInBlogs extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('blogs');
        $table->renameColumn('comments', 'count_comments');
    }
}
