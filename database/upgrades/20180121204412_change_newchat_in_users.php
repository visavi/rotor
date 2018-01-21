<?php

use Phinx\Migration\AbstractMigration;

class ChangeNewchatInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->changeColumn('newchat', 'integer', ['null' => true])
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table
            ->changeColumn('newchat', 'integer')
            ->save();
    }
}
