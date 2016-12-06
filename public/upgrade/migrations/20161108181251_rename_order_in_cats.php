<?php

use Phinx\Migration\AbstractMigration;

class RenameOrderInCats extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('cats');
        $table->renameColumn('order', 'sort');
    }
}
