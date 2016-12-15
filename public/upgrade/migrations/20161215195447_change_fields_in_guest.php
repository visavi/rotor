<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInGuest extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('guest');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('ip', 'string', ['limit' => 15])
            ->changeColumn('time', 'integer')
            ->changeColumn('reply', 'text', ['null' => true])
            ->changeColumn('edit', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('edit_time', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
