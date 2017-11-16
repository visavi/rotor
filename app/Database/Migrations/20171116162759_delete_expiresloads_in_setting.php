<?php

use Phinx\Migration\AbstractMigration;

class DeleteExpiresloadsInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='expiresloads' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('expiresloads', 72);");
    }
}
