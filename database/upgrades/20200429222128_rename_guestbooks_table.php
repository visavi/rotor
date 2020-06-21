<?php

use Phinx\Migration\AbstractMigration;

class RenameGuestbooksTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('guestbooks');
        $table->rename('guestbook')->update();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('guestbook');
        $table->rename('guestbooks')->update();
    }
}
