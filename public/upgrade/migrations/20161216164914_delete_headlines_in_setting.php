<?php

use Phinx\Migration\AbstractMigration;

class DeleteHeadlinesInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='headlines' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('headlines', 10);");
    }
}
