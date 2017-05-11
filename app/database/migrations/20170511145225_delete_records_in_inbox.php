<?php

use Phinx\Migration\AbstractMigration;

class DeleteRecordsInInbox extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("DELETE FROM inbox WHERE user_id='0';");
        $this->execute("DELETE FROM outbox WHERE user_id='0';");
        $this->execute("DELETE FROM trash WHERE user_id='0';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
