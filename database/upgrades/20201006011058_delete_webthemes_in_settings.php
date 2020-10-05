<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteWebthemesInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("DELETE FROM settings WHERE name='webthemes' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('webthemes', 'motor');");
    }
}
