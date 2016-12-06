<?php

use Phinx\Migration\AbstractMigration;

class AddBuildversionToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('buildversion', 0);");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='buildversion' LIMIT 1;");
    }
}
