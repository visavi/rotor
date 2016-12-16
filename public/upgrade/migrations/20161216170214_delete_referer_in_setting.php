<?php

use Phinx\Migration\AbstractMigration;

class DeleteRefererInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='referer' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='showref' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('referer', 300);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('showref', 10);");
    }
}
