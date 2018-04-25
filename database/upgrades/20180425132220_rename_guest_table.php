<?php

use Phinx\Migration\AbstractMigration;

class RenameGuestTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guest');
        $table->rename('guestbooks');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guestbooks');
        $table->rename('guest');
    }
}
