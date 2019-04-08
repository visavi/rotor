<?php

use Phinx\Migration\AbstractMigration;

class AddLanguageFallbackToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('language_fallback', 'ru');");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM settings WHERE name='language_fallback' LIMIT 1;");
    }
}
