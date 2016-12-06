<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInLoads extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('loads');
        $table->renameColumn('loads_id', 'id');
        $table->renameColumn('loads_down', 'down');
        $table->renameColumn('loads_ip', 'ip');
        $table->renameColumn('loads_time', 'time');
    }
}
