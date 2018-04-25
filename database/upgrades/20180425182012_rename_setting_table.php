<?php

use Phinx\Migration\AbstractMigration;

class RenameSettingTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('setting');
        $table->rename('settings');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('settings');
        $table->rename('setting');
    }
}
