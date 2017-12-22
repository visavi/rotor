<?php

use Phinx\Migration\AbstractMigration;

class DeleteBoardInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='boardspost' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='boarddays' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('boardspost', 5);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('boarddays', 30);");
    }
}
