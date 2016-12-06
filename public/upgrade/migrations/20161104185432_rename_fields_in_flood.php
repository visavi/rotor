<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInFlood extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('flood');
        $table->renameColumn('flood_id', 'id');
        $table->renameColumn('flood_user', 'user');
        $table->renameColumn('flood_page', 'page');
        $table->renameColumn('flood_time', 'time');
    }
}
