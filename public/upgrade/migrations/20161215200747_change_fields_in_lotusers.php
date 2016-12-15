<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInLotusers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('lotusers');
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
