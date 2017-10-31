<?php

use Phinx\Migration\AbstractMigration;

class AddLangToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('lang', 'ru');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='lang' LIMIT 1;");
    }
}
