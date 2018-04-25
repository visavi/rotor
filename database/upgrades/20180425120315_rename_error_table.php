<?php

use Phinx\Migration\AbstractMigration;

class RenameErrorTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('error');
        $table->rename('errors');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('errors');
        $table->rename('error');
    }
}
