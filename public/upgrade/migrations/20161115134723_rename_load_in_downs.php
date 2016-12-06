<?php

use Phinx\Migration\AbstractMigration;

class RenameLoadInDowns extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('downs');
        $table->renameColumn('load', 'loads');
    }
}
