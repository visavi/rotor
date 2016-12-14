<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInAdmlog extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('admlog');
        $users
            ->changeColumn('request', 'string', ['null' => true])
            ->changeColumn('referer', 'string', ['null' => true])
            ->changeColumn('ip', 'string', ['limit' => 15])
            ->changeColumn('brow', 'string', ['limit' => 25])
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
