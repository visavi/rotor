<?php

use Phinx\Migration\AbstractMigration;

class DeleteKeywordsInSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM settings WHERE name='keywords';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('keywords', 'Ключевые слова вашего сайта');");
    }
}
