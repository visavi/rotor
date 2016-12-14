<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInBan extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('ban');
        $users->changeColumn('user', 'string', ['limit' => 20, 'null' => true])
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
