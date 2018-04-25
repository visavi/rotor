<?php

use Phinx\Migration\AbstractMigration;

class RenameWallTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('wall');
        $table->rename('walls');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('walls');
        $table->rename('wall');
    }
}
