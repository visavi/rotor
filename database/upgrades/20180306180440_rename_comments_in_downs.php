<?php

use Phinx\Migration\AbstractMigration;

class RenameCommentsInDowns extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('downs');
        $table->renameColumn('comments', 'count_comments');
    }
}
