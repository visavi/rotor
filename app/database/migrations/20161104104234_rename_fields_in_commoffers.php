<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInCommoffers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('commoffers');
        $table->renameColumn('comm_id', 'id');
        $table->renameColumn('comm_offers', 'offers');
        $table->renameColumn('comm_text', 'text');
        $table->renameColumn('comm_user', 'user');
        $table->renameColumn('comm_time', 'time');
        $table->renameColumn('comm_ip', 'ip');
        $table->renameColumn('comm_brow', 'brow');
    }
}
