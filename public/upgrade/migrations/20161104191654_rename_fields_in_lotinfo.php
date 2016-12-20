<?php

use Phinx\Migration\AbstractMigration;

class RenameFieldsInLotinfo extends AbstractMigration
{
    /**
     * Migrate Change.
     */
    public function change()
    {
        $exists = $this->hasTable('lotinfo');
        if ($exists) {
            $table = $this->table('lotinfo');
            $table->renameColumn('lot_id', 'id');
            $table->renameColumn('lot_date', 'date');
            $table->renameColumn('lot_sum', 'sum');
            $table->renameColumn('lot_newnum', 'newnum');
            $table->renameColumn('lot_oldnum', 'oldnum');
            $table->renameColumn('lot_winners', 'winners');
        }
    }
}
