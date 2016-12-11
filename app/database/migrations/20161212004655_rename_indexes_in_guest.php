<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInGuest extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('guest');
        $table->removeIndexByName('guest_time');
        $table->addIndex('time')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('guest');
        $table->removeIndexByName('time');
        $table->addIndex('time', ['name' => 'guest_time'])
            ->save();
    }
}
