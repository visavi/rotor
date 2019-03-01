<?php

use Phinx\Migration\AbstractMigration;

class AddCurrencyToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('currency', 'руб');");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM settings WHERE name='currency' LIMIT 1;");
    }
}
