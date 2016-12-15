<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInNews extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('news');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('image', 'string', ['limit' => 30, 'null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->changeColumn('top', 'boolean', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
