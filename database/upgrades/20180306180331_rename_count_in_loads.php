<?php

use Phinx\Migration\AbstractMigration;

class RenameCountInLoads extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('loads');
        $table->renameColumn('count', 'count_downs');
    }
}
