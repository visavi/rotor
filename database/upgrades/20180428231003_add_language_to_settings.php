<?php

use Phinx\Migration\AbstractMigration;

class AddLanguageToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('language', 'ru');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='language' LIMIT 1;");
    }
}
