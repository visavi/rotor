<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInFlood extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('flood');
        $table->removeIndexByName('flood_user');
        $table->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('flood');
        $table->removeIndexByName('user');
        $table->addIndex('user', ['name' => 'flood_user'])
            ->save();
    }
}
