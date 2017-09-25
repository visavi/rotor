<?php

use Phinx\Migration\AbstractMigration;

class DeleteExpiresmailAndEditstatusInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='expiresmail' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='editstatus' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('expiresmail', '3');");
        $this->execute("INSERT INTO setting (name, value) VALUES ('editstatus', '1');");
    }
}
