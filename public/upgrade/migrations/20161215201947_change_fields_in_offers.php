<?php

use Phinx\Migration\AbstractMigration;

class ChangeFieldsInOffers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $users = $this->table('offers');
        $users
            ->changeColumn('type', 'boolean', ['default' => 0])
            ->changeColumn('text', 'text', ['null' => true])
            ->changeColumn('time', 'integer')
            ->changeColumn('status', 'boolean', ['default' => 0])
            ->changeColumn('closed', 'boolean', ['default' => 0])
            ->changeColumn('text_reply', 'text', ['null' => true])
            ->changeColumn('user_reply', 'string', ['limit' => 20, 'null' => true])
            ->changeColumn('time_reply', 'integer', ['default' => 0])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
