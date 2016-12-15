<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInPollings extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('pollings');
        $users
            ->changeColumn('relate_id', 'integer')
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
