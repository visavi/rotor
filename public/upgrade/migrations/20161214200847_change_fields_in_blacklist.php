<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBlacklist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('blacklist');
        $users->changeColumn('type', 'boolean')
            ->changeColumn('user', 'string', ['limit' => 20, 'null' => true])
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
