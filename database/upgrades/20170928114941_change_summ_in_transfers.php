<?php

use Phinx\Migration\AbstractMigration;

class ChangeSummInTransfers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('transfers');
        $table->renameColumn('summ', 'total');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('transfers');
        $table->renameColumn('total', 'summ');
    }
}
