<?php

use Phinx\Migration\AbstractMigration;

class AddSpamlistToSetting extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("INSERT INTO setting (name, value) VALUES ('spamlist', 10);");

    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute("DELETE FROM setting WHERE name='spamlist' LIMIT 1;");
    }
}
