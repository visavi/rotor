<?php

use Phinx\Migration\AbstractMigration;

class DeleteFileupfotoInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='fileupfoto';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('fileupfoto', 5000);");
    }
}
