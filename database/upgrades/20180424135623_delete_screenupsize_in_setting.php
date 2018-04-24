<?php

use Phinx\Migration\AbstractMigration;

class DeleteScreenupsizeInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='screenupsize';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('screenupsize', 5000);");
    }
}
