<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInIgnoring extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('ignoring');
        $users
            ->changeColumn('text', 'text', ['null' => true])
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
