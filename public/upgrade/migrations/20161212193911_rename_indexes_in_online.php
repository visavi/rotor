<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInOnline extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('online');
        $table->removeIndexByName('online_ip');
        $table->removeIndexByName('online_time');
        $table->removeIndexByName('online_user');
        $table->addIndex('ip')
            ->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('online');
        $table->removeIndexByName('ip');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('ip', ['name' => 'online_ip'])
            ->addIndex('time', ['name' => 'online_time'])
            ->addIndex('user', ['name' => 'online_user'])
            ->save();
    }
}
