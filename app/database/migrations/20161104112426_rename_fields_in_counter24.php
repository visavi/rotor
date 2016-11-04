<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCounter24 extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('counter24');
        $table->renameColumn('count_id', 'id');
        $table->renameColumn('count_hour', 'hour');
        $table->renameColumn('count_hosts', 'hosts');
        $table->renameColumn('count_hits', 'hits');
    }
}
