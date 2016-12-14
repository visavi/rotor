<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInChangemail extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('changemail');
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
