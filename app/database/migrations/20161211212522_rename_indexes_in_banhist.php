<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInBanhist extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('banhist');
        $table->removeIndexByName('ban_time');
        $table->removeIndexByName('ban_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('banhist');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'ban_time'])
            ->addIndex('user', ['name' => 'ban_user'])
            ->save();
    }
}
