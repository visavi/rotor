<?php

use Phinx\Migration\AbstractMigration;

class DeleteValuesInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='resmiles' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='editnickpoint' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='includenick' LIMIT 1;");
        $this->execute("DELETE FROM setting WHERE name='regmail' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('resmiles', 5);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('editnickpoint', 300);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('includenick', 1);");
        $this->execute("INSERT INTO setting (name, value) VALUES ('regmail', 0);");
    }
}
