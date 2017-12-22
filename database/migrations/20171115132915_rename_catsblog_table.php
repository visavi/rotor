<?php

use Phinx\Migration\AbstractMigration;

class RenameCatsblogTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('catsblog');
        $table->rename('categories');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('categories');
        $table->rename('catsblog');
    }
}
