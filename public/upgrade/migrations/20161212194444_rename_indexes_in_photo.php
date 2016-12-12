<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInPhoto extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('photo');
        $table->removeIndexByName('photo_time');
        $table->removeIndexByName('photo_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('photo');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'photo_time'])
            ->addIndex('user', ['name' => 'photo_user'])
            ->save();
    }
}
