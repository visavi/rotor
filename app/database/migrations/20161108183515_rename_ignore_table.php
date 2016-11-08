<?php

use Phinx\Migration\AbstractMigration;

class RenameIgnoreTable extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('ignore');
        $table->rename('ignoring');
    }
}
