<?php

use Phinx\Migration\AbstractMigration;

class RenameGuestbooksTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guestbooks');
        $table->rename('guestbook')->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guestbook');
        $table->rename('guestbooks')->update();
    }
}
