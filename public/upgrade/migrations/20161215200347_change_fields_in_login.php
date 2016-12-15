<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInLogin extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('login');
        $users
            ->changeColumn('time', 'integer')
            ->changeColumn('type', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
