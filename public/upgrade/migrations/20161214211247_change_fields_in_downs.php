<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInDowns extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('downs');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('site', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('screen', 'string', ['limit' => 50, 'null' => true])
            ->changeColumn('last_load', 'integer', ['default' => 0])
            ->changeColumn('notice', 'text', ['null' => true])
            ->changeColumn('app', 'boolean', ['default' => 0])
            ->changeColumn('active', 'boolean', ['default' => 0])
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
