<?php

use Phinx\Migration\AbstractMigration;

class DeleteMaxbantimeInSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM setting WHERE name='maxbantime' LIMIT 1;");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('maxbantime', 43200);");
    }
}
