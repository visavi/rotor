<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteBuildversionInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("DELETE FROM settings WHERE name='buildversion' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('buildversion', '0');");
    }
}
