<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCounter31 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('counter31');
        $table->renameColumn('count_id', 'id');
        $table->renameColumn('count_days', 'days');
        $table->renameColumn('count_hosts', 'hosts');
        $table->renameColumn('count_hits', 'hits');
    }
}
