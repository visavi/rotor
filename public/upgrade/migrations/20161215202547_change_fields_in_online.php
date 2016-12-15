<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInOnline extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('online');
        $users
            ->changeColumn('time', 'integer')
            ->changeColumn('user', 'string', ['limit' => 20, 'null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
