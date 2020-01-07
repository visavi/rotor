<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteWallmaxpostInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("DELETE FROM settings WHERE name='wallmaxpost' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('wallmaxpost', '1000');");
    }
}
