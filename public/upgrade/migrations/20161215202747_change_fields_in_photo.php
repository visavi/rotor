<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInPhoto extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('photo');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
