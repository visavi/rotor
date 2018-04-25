<?php

use Phinx\Migration\AbstractMigration;

class RenameVoteTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('vote');
        $table->rename('votes');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('votes');
        $table->rename('vote');
    }
}
