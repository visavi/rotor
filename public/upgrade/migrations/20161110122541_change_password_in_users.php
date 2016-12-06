<?php

use Phinx\Migration\AbstractMigration;

class ChangePasswordInUsers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('users');
        $table->renameColumn('pass', 'password');
        $table->changeColumn('password', 'string', array('limit' => 128))
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('users');
        $table->renameColumn('password', 'pass');
        $table->changeColumn('pass', 'string', array('limit' => 40))
            ->save();
    }
}
