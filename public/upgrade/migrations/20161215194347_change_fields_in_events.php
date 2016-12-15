<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

class ChangeFieldsInEvents extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('events');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('image', 'string', ['limit' => 30, 'null' => true])
            ->changeColumn('top', 'boolean', ['default' => 0])
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
