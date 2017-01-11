<?php

use Phinx\Migration\AbstractMigration;

class DeleteSessionInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='session' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('session', 1);");
    }
}
