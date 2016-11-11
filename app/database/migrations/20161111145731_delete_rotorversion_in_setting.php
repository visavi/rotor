<?php

use Phinx\Migration\AbstractMigration;

class DeleteRotorversionInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='rotorversion' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('rotorversion', '4.5.4');");
    }
}
