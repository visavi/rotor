<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInIgnoring extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('ignoring');
        $table->removeIndexByName('ignore_time');
        $table->removeIndexByName('ignore_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('ignoring');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'ignore_time'])
            ->addIndex('user', ['name' => 'ignore_user'])
            ->save();
    }
}
