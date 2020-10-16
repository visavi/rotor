<?php

use Phinx\Migration\AbstractMigration;

class ChangeSmileInStickers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("UPDATE stickers SET name='/uploads/stickers/smile.gif' WHERE code=':)';");
        $this->execute("UPDATE stickers SET name='/uploads/stickers/sad.gif' WHERE code=':(';");

        clearCache('stickers');
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("UPDATE stickers SET name='/uploads/stickers/).gif' WHERE code=':)';");
        $this->execute("UPDATE stickers SET name='/uploads/stickers/(.gif' WHERE code=':(';");

        clearCache('stickers');
    }
}
