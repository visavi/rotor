<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInWall extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('wall');
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
