<?php

use Phinx\Migration\AbstractMigration;

class AddMaxfilesToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxfiles', 5);");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='maxfiles' LIMIT 1;");
    }
}
