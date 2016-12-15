<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInNotice extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('notice');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('user', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('protect', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
