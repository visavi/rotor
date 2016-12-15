<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInFlood extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('flood');
        $users
            ->changeColumn('time', 'integer')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
