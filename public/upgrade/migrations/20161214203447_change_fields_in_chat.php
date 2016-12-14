<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInChat extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('chat');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('ip', 'string', ['limit' => 15])
            ->changeColumn('edit', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('edit_time', 'integer', ['default' => 0])
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
