<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInLoads extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('loads');

        $table->renameColumn('down', 'down_id');
        $table->renameColumn('time', 'created_at');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('loads');

        $table->renameColumn('down_id', 'down');
        $table->renameColumn('created_at', 'time');
    }
}
