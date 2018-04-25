<?php

use Phinx\Migration\AbstractMigration;

class RenameNoteTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('note');
        $table->rename('notes');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('notes');
        $table->rename('note');
    }
}
