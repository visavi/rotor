<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInTransfers extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('transfers');
        $table->renameColumn('trans_id', 'id');
        $table->renameColumn('trans_user', 'user');
        $table->renameColumn('trans_login', 'login');
        $table->renameColumn('trans_text', 'text');
        $table->renameColumn('trans_summ', 'summ');
        $table->renameColumn('trans_time', 'time');
    }
}
