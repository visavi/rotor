<?php

use Phinx\Migration\AbstractMigration;

class RenameCommentsInNews extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('news');
        $table->renameColumn('comments', 'count_comments');
    }
}
