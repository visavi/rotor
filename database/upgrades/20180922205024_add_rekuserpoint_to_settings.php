<?php

use Phinx\Migration\AbstractMigration;

class AddRekuserpointToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('rekuserpoint', 50);");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='rekuserpoint' LIMIT 1;");
    }
}
