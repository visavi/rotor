<?php

use App\Models\Message;
use Phinx\Migration\AbstractMigration;

class DeleteLimitmailInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM settings WHERE name='limitmail' LIMIT 1;");
        $this->execute("DELETE FROM settings WHERE name='limitoutmail' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('limitmail', 300);");
        $this->execute("INSERT INTO settings (name, value) VALUES ('limitoutmail', 100);");
    }
}
