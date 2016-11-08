<?php

use Phinx\Migration\AbstractMigration;

class RenameOrderInCatsblog extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('catsblog');
        $table->renameColumn('order', 'sort');
    }
}
