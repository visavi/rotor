<?php

use Phinx\Migration\AbstractMigration;

class RenameContactTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('contact');
        $table->rename('contacts');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('contacts');
        $table->rename('contact');
    }
}
