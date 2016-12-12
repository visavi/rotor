<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInTrash extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('trash');
        $table->removeIndexByName('trash_time');
        $table->removeIndexByName('trash_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('trash');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'trash_time'])
            ->addIndex('user', ['name' => 'trash_user'])
            ->save();
    }
}
