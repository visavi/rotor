<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVote extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('vote');
        $users
            ->changeColumn('closed', 'boolean', ['default' => 0])
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
