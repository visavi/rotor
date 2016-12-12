<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInChat extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('chat');
        $table->removeIndexByName('chat_time');
        $table->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('chat');
        $table->removeIndexByName('time');
        $table->addIndex('time', ['name' => 'chat_time'])
            ->save();
    }
}
