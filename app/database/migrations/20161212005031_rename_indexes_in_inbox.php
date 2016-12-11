<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInInbox extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('inbox');
        $table->removeIndexByName('inbox_time');
        $table->removeIndexByName('inbox_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('inbox');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'inbox_time'])
            ->addIndex('user', ['name' => 'inbox_user'])
            ->save();
    }
}
