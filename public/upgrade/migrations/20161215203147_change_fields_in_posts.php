<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInPosts extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('posts');
        $users
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('time', 'integer')
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
