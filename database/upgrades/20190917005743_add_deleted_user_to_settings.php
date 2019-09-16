<?php

use Phinx\Migration\AbstractMigration;

class AddDeletedUserToSettings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO settings (name, value) VALUES ('deleted_user', 'Удаленный');");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM settings WHERE name='deleted_user' LIMIT 1;");
    }
}
