<?php

use Phinx\Migration\AbstractMigration;

class UpdateAvatarInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->execute("UPDATE `users` SET avatar='';");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
