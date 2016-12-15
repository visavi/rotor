<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInTopics extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('topics');
        $users
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->changeColumn('locked', 'boolean', ['default' => 0])
            ->changeColumn('last_user', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('last_time', 'integer', ['default' => 0])
            ->changeColumn('moderators', 'string', ['null' => true])
            ->changeColumn('note', 'string', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
