<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddAppInstalledToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('app_installed', '1');");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM settings WHERE name='app_installed' LIMIT 1;");
    }
}
