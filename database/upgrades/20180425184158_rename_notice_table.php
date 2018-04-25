<?php

use Phinx\Migration\AbstractMigration;

class RenameNoticeTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('notice');
        $table->rename('notices');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('notices');
        $table->rename('notice');
    }
}
