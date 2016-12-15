<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInRekuser extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('rekuser');
        $users
            ->changeColumn('color', 'string', ['limit' => 10, 'null' => true])
            ->changeColumn('bold', 'boolean', ['default' => 0])
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
