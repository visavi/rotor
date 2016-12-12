<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInWall extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('wall');
        $table->removeIndexByName('wall_user');
        $table->addIndex('user')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('visit');
        $table->removeIndexByName('user');
        $table->addIndex('user', ['name' => 'wall_user'])
            ->save();
    }
}
