<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInBank extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $table = $this->table('bank');
        if ($table->exists()) {
            $table->renameColumn('bank_id', 'id');
            $table->renameColumn('bank_user', 'user');
            $table->renameColumn('bank_sum', 'sum');
            $table->renameColumn('bank_oper', 'oper');
            $table->renameColumn('bank_time', 'time');
        }
    }
}
