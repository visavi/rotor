<?php

use Phinx\Migration\AbstractMigration;

class RenameDescInForums extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('forums');
        $table->renameColumn('desc', 'description');
    }
}
