<?php

use Phinx\Migration\AbstractMigration;

class RenameCommentsInPhoto extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('photo');
        $table->renameColumn('comments', 'count_comments');
    }
}
