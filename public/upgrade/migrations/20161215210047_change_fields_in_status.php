<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInStatus extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('status');
        $users
            ->changeColumn('color', 'string', ['limit' => 10, 'null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
