<?php

use Phinx\Migration\AbstractMigration;

class RenameOrderInForums extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('forums');
        $table->renameColumn('order', 'sort');
    }
}
