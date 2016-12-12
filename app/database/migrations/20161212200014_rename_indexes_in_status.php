<?php

use Phinx\Migration\AbstractMigration;

class RenameIndexesInStatus extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('status');
        $table->removeIndexByName('status_point');
        $table->removeIndexByName('status_topoint');
        $table->addIndex('point')
            ->addIndex('topoint')
            ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('status');
        $table->removeIndexByName('point');
        $table->removeIndexByName('topoint');
        $table->addIndex('point', ['name' => 'status_point'])
            ->addIndex('topoint', ['name' => 'status_topoint'])
            ->save();
    }
}
