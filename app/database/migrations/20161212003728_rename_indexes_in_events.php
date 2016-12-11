<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInEvents extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('events');
        $table->removeIndexByName('event_time');
        $table->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('events');
        $table->removeIndexByName('time');
        $table->addIndex('time', ['name' => 'event_time'])
            ->save();
    }
}
