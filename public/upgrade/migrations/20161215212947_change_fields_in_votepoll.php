<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInVotepoll extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('votepoll');
        $users
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
