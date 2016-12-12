<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInOutbox extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('outbox');
        $table->removeIndexByName('outbox_time');
        $table->removeIndexByName('outbox_user');
        $table->addIndex('time')
            ->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('outbox');
        $table->removeIndexByName('time');
        $table->removeIndexByName('user');
        $table->addIndex('time', ['name' => 'outbox_time'])
            ->addIndex('user', ['name' => 'outbox_user'])
            ->save();
    }
}
