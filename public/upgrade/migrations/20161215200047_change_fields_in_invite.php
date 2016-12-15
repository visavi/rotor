<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInInvite extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('invite');
        $users
            ->changeColumn('invited', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('used', 'boolean', ['default' => 0])
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
