<?php

use Phinx\Migration\AbstractMigration;

class RenameCatsTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('cats');
        $table->rename('loads');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('loads');
        $table->rename('cats');
    }
}
