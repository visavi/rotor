<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCommentsPerPageToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('comments_per_page', '10');");

    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("DELETE FROM settings WHERE name='comments_per_page' LIMIT 1;");
    }
}
