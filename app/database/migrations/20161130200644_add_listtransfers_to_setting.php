<?php

use Phinx\Migration\AbstractMigration;

class AddListtransfersToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('listtransfers', 10);");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='listtransfers' LIMIT 1;");
    }
}
