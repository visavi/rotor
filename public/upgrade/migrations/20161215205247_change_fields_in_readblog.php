<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInReadblog extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('readblog');
        $users
            ->changeColumn('ip', 'string', ['limit' => 15])
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
