<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInLogin extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('login');
        $table->removeIndexByName('login_time');
        $table->removeIndexByName('login_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('login');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'login_time'])
            ->addIndex('user', ['name' => 'login_user'])
            ->save();
    }
}
