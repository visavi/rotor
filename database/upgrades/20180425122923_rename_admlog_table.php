<?php

use Phinx\Migration\AbstractMigration;

class RenameAdmlogTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('admlog');
        $table->rename('logs');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('logs');
        $table->rename('admlog');
    }
}
