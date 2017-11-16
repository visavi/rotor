<?php

use Phinx\Migration\AbstractMigration;

class DeleteMaxlogdatInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='maxlogdat' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxlogdat', 10);");
    }
}
