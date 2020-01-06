<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class DeleteCommentsInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("DELETE FROM settings WHERE name='postgallery' LIMIT 1;");
        $this->execute("DELETE FROM settings WHERE name='blogcomm' LIMIT 1;");
        $this->execute("DELETE FROM settings WHERE name='downcomm' LIMIT 1;");
        $this->execute("DELETE FROM settings WHERE name='postcommoffers' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('postgallery', '10');");
        $this->execute("INSERT INTO settings (name, value) VALUES ('blogcomm', '10');");
        $this->execute("INSERT INTO settings (name, value) VALUES ('downcomm', '10');");
        $this->execute("INSERT INTO settings (name, value) VALUES ('postcommoffers', '10');");
    }
}
