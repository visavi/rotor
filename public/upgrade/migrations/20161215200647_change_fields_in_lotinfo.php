<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInLotinfo extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('lotinfo');
        $users
            ->changeColumn('winners', 'string', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
