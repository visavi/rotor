<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInTransfers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('transfers');
        $table->removeIndexByName('trans_login');
        $table->removeIndexByName('trans_time');
        $table->removeIndexByName('trans_user');
        $table->addIndex('login')
            ->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('transfers');
        $table->removeIndexByName('login');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('login', ['name' => 'trans_login'])
            ->addIndex('time', ['name' => 'trans_time'])
            ->addIndex('user', ['name' => 'trans_user'])
            ->save();
    }
}
