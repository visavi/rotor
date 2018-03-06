<?php

use Phinx\Migration\AbstractMigration;

class RenameCommentsInOffers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('offers');
        $table->renameColumn('comments', 'count_comments');
    }
}
